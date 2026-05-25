import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:image_picker/image_picker.dart';
import 'package:mask_text_input_formatter/mask_text_input_formatter.dart';
import 'package:provider/provider.dart';
import 'dart:io';
import '../providers/auth_provider.dart';
import '../widgets/logo_image.dart';
import '../services/api_client.dart';
import '../services/cep_service.dart';

// Mesmo padrão visual do login — claro e profissional
const _primary = Color(0xFF198754);
const _primaryLight = Color(0xFFE8F5E9);
const _surface = Color(0xFFFAFCFB);
const _cardBg = Color(0xFFFFFFFF);
const _textPrimary = Color(0xFF1A2E22);
const _textSecondary = Color(0xFF5A6B63);
const _border = Color(0xFFE2E8E6);
const _inputBg = Color(0xFFF4F7F5);

// Máscaras: CPF 11 dígitos, telefone 11 dígitos (00) 00000-0000, CEP 8 dígitos
final _cpfMask = MaskTextInputFormatter(mask: '###.###.###-##', filter: {'#': RegExp(r'[0-9]')});
final _phoneMask = MaskTextInputFormatter(mask: '(##) #####-####', filter: {'#': RegExp(r'[0-9]')});
final _cepMask = MaskTextInputFormatter(mask: '#####-###', filter: {'#': RegExp(r'[0-9]')});

/// Configuração de um quarto (suíte, camas, pessoas) para o passo da cota.
/// suite/people: -1 = "Selecione" (não preenchido); suite 0=Não 1=Sim; people 1-10.
class RoomConfig {
  int suite; // -1 = Selecione, 0 = Não, 1 = Sim
  int doubleBed; // 0-2
  int singleBed; // 0-5
  int sofaBed; // 0-2
  int bunkBed; // 0-5
  int people; // -1 = Selecione, 1-10
  RoomConfig({
    this.suite = -1,
    this.doubleBed = 0,
    this.singleBed = 0,
    this.sofaBed = 0,
    this.bunkBed = 0,
    this.people = -1,
  });
  int get totalBeds => doubleBed * 2 + singleBed + sofaBed * 2 + bunkBed * 2;
  bool get isValid => suite >= 0 && people >= 1 && people <= totalBeds && totalBeds >= 1;
  RoomConfig copyWith({int? suite, int? doubleBed, int? singleBed, int? sofaBed, int? bunkBed, int? people}) {
    return RoomConfig(
      suite: suite ?? this.suite,
      doubleBed: doubleBed ?? this.doubleBed,
      singleBed: singleBed ?? this.singleBed,
      sofaBed: sofaBed ?? this.sofaBed,
      bunkBed: bunkBed ?? this.bunkBed,
      people: people ?? this.people,
    );
  }
}

/// Dados de uma semana no passo da cota (autorização, datas, comprovante).
/// authorize: null/''='Selecione', 'yes'=Sim, 'no'=Não
class WeekBlockData {
  String? authorize;
  String? startDay;   // 01-31
  String? endDay;     // calculado: startDay + 7
  String? month;      // 01-12
  String? year;
  File? proofFile;

  WeekBlockData({
    this.authorize,
    this.startDay,
    this.endDay,
    this.month,
    this.year,
    this.proofFile,
  });

  bool get isAuthorized => authorize == 'yes';
  bool get isValid {
    final a = authorize;
    if (a == null || a.isEmpty) return false;
    if (a == 'no') return true;
    return startDay != null && startDay!.isNotEmpty &&
           month != null && month!.isNotEmpty &&
           year != null && year!.isNotEmpty &&
           proofFile != null;
  }

  WeekBlockData copyWith({
    String? authorize,
    String? startDay,
    String? endDay,
    String? month,
    String? year,
    File? proofFile,
  }) {
    return WeekBlockData(
      authorize: authorize ?? this.authorize,
      startDay: startDay ?? this.startDay,
      endDay: endDay ?? this.endDay,
      month: month ?? this.month,
      year: year ?? this.year,
      proofFile: proofFile ?? this.proofFile,
    );
  }
}

/// Calcula o dia de término: dia inicial + 7 (regra do web)
String _computeEndDay(String? startDay, String? month, String? year) {
  if (startDay == null || startDay.isEmpty) return '';
  final start = int.tryParse(startDay);
  if (start == null) return '';
  if (month != null && month.isNotEmpty && year != null && year.isNotEmpty) {
    final y = int.tryParse(year) ?? DateTime.now().year;
    final m = int.tryParse(month) ?? 1;
    try {
      final startDate = DateTime(y, m, start.clamp(1, 28));
      final endDate = startDate.add(const Duration(days: 7));
      return endDate.day.toString().padLeft(2, '0');
    } catch (_) {
      int end = start + 7;
      if (end > 31) end = ((end - 1) % 31) + 1;
      return end.toString().padLeft(2, '0');
    }
  }
  int end = start + 7;
  if (end > 31) end = ((end - 1) % 31) + 1;
  return end.toString().padLeft(2, '0');
}

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _pageController = PageController();
  final _formKey = GlobalKey<FormState>();
  final _formKeyStep1 = GlobalKey<FormState>();
  final _formKeyStep2 = GlobalKey<FormState>();
  final _formKeyStep3 = GlobalKey<FormState>();
  int _currentStep = 0;
  bool _loading = false;
  bool _loadingCep = false;

  // Informações da cota (quando has_quota 1 ou 2)
  final _formKeyQuotaInfo = GlobalKey<FormState>();
  int? _ownerQuotaWeeksCount; // null = "Selecione"
  int? _ownerHotelId;
  int? _ownerQuotaRooms; // null = "Selecione a quantidade"
  final _ownerQuotaSize = TextEditingController();
  String _ownerQuotaSeasonality = 'media';
  String _ownerQuotaType = 'fixa';
  bool _ownerQuotaBreakfast = true;
  List<String> _allowedUses = [];
  String? _quotaStatus; // null = "Selecione o status da cota"; 'paid' = Quitada (oculta Prazo), 'unpaid' = Não quitada
  DateTime? _quotaPaymentDeadline;
  File? _quotaContractFile; // documento de confirmação da posse da cota
  File? _hospitalityAuthorizationTermFile; // termo de autorização de hospedagem
  bool? _hotelInOperation; // hotel do gestor em funcionamento? true = Sim, false = Não
  final _ownerQuotaObservations = TextEditingController();
  Map<int, RoomConfig> _ownerRoomConfig = {}; // configuração por quarto (1..n)
  Map<int, WeekBlockData> _ownerWeekBlocks = {}; // blocos de semana (1..n)
  // Comodidades da cota (null = Selecione, true = Sim, false = Não)
  Map<String, bool?> _quotaAmenities = {};
  // Fracionamento por semana: semana -> tipo (7, 3_4, 4_3, etc.)
  final Map<int, String> _fractionTypePerWeek = {};
  // semana -> lista de ações por período (rent, exchange, rent_exchange)
  final Map<int, List<String>> _fractionPeriodActionsPerWeek = {};

  // Dados do formulário
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _confirmPassword = TextEditingController();
  final _fullName = TextEditingController();
  final _cpf = TextEditingController();
  final _phone = TextEditingController();
  final _whatsapp = TextEditingController();
  final _cep = TextEditingController();
  final _street = TextEditingController();
  final _neighborhood = TextEditingController();
  final _city = TextEditingController();
  final _state = TextEditingController();
  final _houseNumber = TextEditingController();

  DateTime? _birthDate;
  String _documentType = 'rg';
  File? _userPhoto;
  File? _documentPhoto;
  int _hasQuota = -1; // 0=não, 1=sim proprietário, 2=gestor
  String? _profileType; // curioso, inteligente, sabio
  bool _acceptTerms = false;
  bool _acceptPromotional = false;

  List<TextEditingController> get _controllers => [
        _name, _email, _password, _confirmPassword, _fullName, _cpf, _phone,
        _whatsapp, _cep, _street, _neighborhood, _city, _state, _houseNumber,
        _ownerQuotaSize, _ownerQuotaObservations,
      ];

  int get _totalSteps {
    int n = 7; // login, personal, address, documents, quota, profile, terms
    if (_hasQuota == 1 || _hasQuota == 2) n += 1; // quota info
    if ((_hasQuota == 1 || _hasQuota == 2) &&
        (_profileType == 'inteligente' || _profileType == 'sabio') &&
        _ownerQuotaWeeksCount != null) {
      n += _ownerQuotaWeeksCount!; // fraction steps
    }
    return n;
  }

  @override
  void initState() {
    super.initState();
    _cep.addListener(_onCepChanged);
  }

  void _onCepChanged() {
    final digits = _cep.text.replaceAll(RegExp(r'[^0-9]'), '');
    if (digits.length == 8) _fetchCep(digits);
  }

  Future<void> _fetchCep(String digits) async {
    if (_loadingCep) return;
    setState(() => _loadingCep = true);
    try {
      final r = await CepService.fetchByCep(digits);
      if (!mounted) return;
      if (r != null) {
        setState(() {
          _street.text = r.logradouro ?? '';
          _neighborhood.text = r.bairro ?? '';
          _city.text = r.localidade ?? '';
          _state.text = r.uf ?? '';
        });
      }
    } finally {
      if (mounted) setState(() => _loadingCep = false);
    }
  }

  @override
  void dispose() {
    _cep.removeListener(_onCepChanged);
    _pageController.dispose();
    for (final c in _controllers) {
      c.dispose();
    }
    super.dispose();
  }

  void _nextStep() {
    final pages = _buildPages();
    if (_currentStep < pages.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
      setState(() => _currentStep++);
    }
  }

  void _prevStep() {
    if (_currentStep > 0) {
      _pageController.previousPage(
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeInOut,
      );
      setState(() => _currentStep--);
    }
  }

  List<Widget> _buildPages() {
    final pages = <Widget>[
      _Step1Login(formKey: _formKeyStep1, name: _name, email: _email, password: _password, confirmPassword: _confirmPassword, onNext: _nextStep),
      _Step2Personal(formKey: _formKeyStep2, fullName: _fullName, cpf: _cpf, phone: _phone, whatsapp: _whatsapp, birthDate: _birthDate, onBirthDateChanged: (d) => setState(() => _birthDate = d), onNext: _nextStep, onPrev: _prevStep),
      _Step3Address(formKey: _formKeyStep3, cep: _cep, street: _street, neighborhood: _neighborhood, city: _city, state: _state, houseNumber: _houseNumber, loadingCep: _loadingCep, onNext: _nextStep, onPrev: _prevStep),
      _Step4Documents(documentType: _documentType, onDocumentTypeChanged: (v) => setState(() => _documentType = v), userPhoto: _userPhoto, documentPhoto: _documentPhoto, onUserPhotoChanged: (f) => setState(() => _userPhoto = f), onDocumentPhotoChanged: (f) => setState(() => _documentPhoto = f), onNext: _nextStep, onPrev: _prevStep, canProceed: _userPhoto != null && _documentPhoto != null),
      _Step5Quota(hasQuota: _hasQuota, onHasQuotaChanged: (v) => setState(() => _hasQuota = v), onNext: _nextStep, onPrev: _prevStep, canProceed: _hasQuota >= 0),
    ];
    if (_hasQuota == 1 || _hasQuota == 2) {
      pages.add(_StepQuotaInfo(
        formKey: _formKeyQuotaInfo,
        hasQuota: _hasQuota,
        ownerHotelId: _ownerHotelId,
        onOwnerHotelIdChanged: (v) => setState(() => _ownerHotelId = v),
        hotelInOperation: _hotelInOperation,
        onHotelInOperationChanged: (v) => setState(() => _hotelInOperation = v),
        quotaContractFile: _quotaContractFile,
        onQuotaContractFileChanged: (f) => setState(() => _quotaContractFile = f),
        hospitalityAuthorizationTermFile: _hospitalityAuthorizationTermFile,
        onHospitalityAuthorizationTermFileChanged: (f) => setState(() => _hospitalityAuthorizationTermFile = f),
        quotaStatus: _quotaStatus,
        onQuotaStatusChanged: (v) => setState(() => _quotaStatus = v),
        quotaPaymentDeadline: _quotaPaymentDeadline,
        onQuotaPaymentDeadlineChanged: (d) => setState(() => _quotaPaymentDeadline = d),
        ownerQuotaRooms: _ownerQuotaRooms,
        onOwnerQuotaRoomsChanged: (v) {
          setState(() {
            _ownerQuotaRooms = v;
            if (v != null) {
              for (int i = 1; i <= v; i++) {
                _ownerRoomConfig[i] ??= RoomConfig();
              }
              _ownerRoomConfig.removeWhere((k, _) => k > v);
            } else {
              _ownerRoomConfig.clear();
            }
          });
        },
        ownerQuotaWeeksCount: _ownerQuotaWeeksCount,
        onOwnerQuotaWeeksCountChanged: (v) {
          setState(() {
            _ownerQuotaWeeksCount = v;
            if (v != null) {
              for (int i = 1; i <= v; i++) {
                _ownerWeekBlocks[i] ??= WeekBlockData();
              }
              _ownerWeekBlocks.removeWhere((k, _) => k > v);
            } else {
              _ownerWeekBlocks.clear();
            }
          });
        },
        ownerWeekBlocks: _ownerWeekBlocks,
        onOwnerWeekBlockChanged: (idx, data) {
          setState(() => _ownerWeekBlocks[idx] = data);
        },
        ownerRoomConfig: _ownerRoomConfig,
        onOwnerRoomConfigChanged: (roomIndex, config) {
          setState(() => _ownerRoomConfig[roomIndex] = config);
        },
        ownerQuotaSize: _ownerQuotaSize,
        quotaAmenities: _quotaAmenities,
        onQuotaAmenityChanged: (key, value) {
          setState(() => _quotaAmenities[key] = value);
        },
        ownerQuotaSeasonality: _ownerQuotaSeasonality,
        onOwnerQuotaSeasonalityChanged: (v) => setState(() => _ownerQuotaSeasonality = v),
        ownerQuotaType: _ownerQuotaType,
        onOwnerQuotaTypeChanged: (v) => setState(() => _ownerQuotaType = v),
        ownerQuotaBreakfast: _ownerQuotaBreakfast,
        onOwnerQuotaBreakfastChanged: (v) => setState(() => _ownerQuotaBreakfast = v),
        allowedUses: _allowedUses,
        onAllowedUsesChanged: (v) => setState(() => _allowedUses = v),
        ownerQuotaObservations: _ownerQuotaObservations,
        onNext: _nextStep,
        onPrev: _prevStep,
      ));
    }
    pages.add(_Step6Profile(profileType: _profileType, onProfileTypeChanged: (v) => setState(() => _profileType = v), onNext: _nextStep, onPrev: _prevStep, canProceed: _profileType != null));
    if ((_hasQuota == 1 || _hasQuota == 2) && (_profileType == 'inteligente' || _profileType == 'sabio') && _ownerQuotaWeeksCount != null && _ownerQuotaWeeksCount! >= 1) {
      for (int w = 1; w <= _ownerQuotaWeeksCount!; w++) {
        final week = w;
        pages.add(_StepFraction(
          weekNumber: week,
          profileType: _profileType!,
          fractionType: _fractionTypePerWeek[week] ?? '7',
          onFractionTypeChanged: (v) => setState(() => _fractionTypePerWeek[week] = v),
          periodActions: _fractionPeriodActionsPerWeek[week] ?? [],
          onPeriodActionsChanged: (v) => setState(() => _fractionPeriodActionsPerWeek[week] = v),
          onNext: _nextStep,
          onPrev: _prevStep,
        ));
      }
    }
    pages.add(_Step7Terms(acceptTerms: _acceptTerms, acceptPromotional: _acceptPromotional, onAcceptTermsChanged: (v) => setState(() => _acceptTerms = v), onAcceptPromotionalChanged: (v) => setState(() => _acceptPromotional = v), onPrev: _prevStep, onSubmit: _submit, loading: _loading));
    return pages;
  }

  Future<void> _submit() async {
    if (!_acceptTerms) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Aceite os termos para continuar.'), behavior: SnackBarBehavior.floating),
      );
      return;
    }
    if (_userPhoto == null || _documentPhoto == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Envie sua foto e o documento.'), behavior: SnackBarBehavior.floating),
      );
      return;
    }
    setState(() => _loading = true);
    try {
      final api = ApiClient();
      final body = {
        'name': _name.text.trim(),
        'email': _email.text.trim(),
        'password': _password.text,
        'password_confirmation': _confirmPassword.text,
        'full_name': _fullName.text.trim(),
        'cpf': _cpf.text.trim(),
        'phone': _phone.text.trim(),
        'whatsapp': _whatsapp.text.trim().isEmpty ? null : _whatsapp.text.trim(),
        'ingress_date': _birthDate != null ? _birthDate!.toIso8601String().split('T').first : null,
        'cep': _cep.text.trim(),
        'street': _street.text.trim(),
        'neighborhood': _neighborhood.text.trim(),
        'city': _city.text.trim(),
        'state': _state.text.trim(),
        'house_number': _houseNumber.text.trim(),
        'document_type': _documentType,
        'user_photo_base64': _userPhoto != null ? base64Encode(await File(_userPhoto!.path).readAsBytes()) : null,
        'document_photo_base64': _documentPhoto != null ? base64Encode(await File(_documentPhoto!.path).readAsBytes()) : null,
        'has_quota': _hasQuota >= 0 ? _hasQuota : 0,
        'profile_type': _profileType ?? 'curioso',
        'accept_terms': true,
        'accept_promotional_periods': _acceptPromotional,
      };
      final res = await api.postJson('/register', body: body, auth: false);
      if (res['success'] == true && mounted) {
        await context.read<AuthProvider>().login(_email.text.trim(), _password.text);
        if (mounted) Navigator.of(context).popUntil((r) => r.isFirst);
        return;
      }
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(res['message']?.toString() ?? 'Erro ao cadastrar. Tente novamente.'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erro: $e'), behavior: SnackBarBehavior.floating),
        );
      }
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _surface,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: _textPrimary),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Text(
          'Passo ${_currentStep + 1} de ${_buildPages().length}',
          style: const TextStyle(color: _textSecondary, fontSize: 14, fontWeight: FontWeight.w500),
        ),
      ),
      body: SafeArea(
        child: Column(
          children: [
            const LogoImage(height: 120),
            const SizedBox(height: 8),
            Expanded(
              child: Builder(
                builder: (context) {
                  final pages = _buildPages();
                  if (_currentStep >= pages.length && pages.isNotEmpty) {
                    WidgetsBinding.instance.addPostFrameCallback((_) {
                      if (mounted) setState(() => _currentStep = pages.length - 1);
                    });
                  }
                  return PageView.builder(
                    controller: _pageController,
                    physics: const NeverScrollableScrollPhysics(),
                    itemCount: pages.length,
                    itemBuilder: (context, index) => pages[index],
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// --- Step 1: Dados de acesso ---
class _Step1Login extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final TextEditingController name;
  final TextEditingController email;
  final TextEditingController password;
  final TextEditingController confirmPassword;
  final VoidCallback onNext;

  const _Step1Login({
    required this.formKey,
    required this.name,
    required this.email,
    required this.password,
    required this.confirmPassword,
    required this.onNext,
  });

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Dados de acesso',
      subtitle: 'Nome de usuário, e-mail e senha (mín. 8 caracteres)',
      child: Form(
        key: formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _Label('Nome de usuário *'),
            const SizedBox(height: 6),
            _Input(
              controller: name,
              hint: 'Mínimo 6 caracteres',
              validator: (v) => v == null || v.trim().length < 6 ? 'Mínimo 6 caracteres' : null,
            ),
            const SizedBox(height: 18),
            _Label('E-mail *'),
            const SizedBox(height: 6),
            _Input(
              controller: email,
              hint: 'seu@email.com',
              keyboard: TextInputType.emailAddress,
              validator: (v) {
                if (v == null || v.trim().isEmpty) return 'Informe o e-mail';
                if (!v.contains('@') || !v.contains('.')) return 'E-mail inválido';
                return null;
              },
            ),
            const SizedBox(height: 18),
            _Label('Senha *'),
            const SizedBox(height: 6),
            _Input(controller: password, hint: 'Mínimo 8 caracteres', obscure: true, validator: (v) => (v == null || v.length < 8) ? 'Mínimo 8 caracteres' : null),
            const SizedBox(height: 18),
            _Label('Confirmar senha *'),
            const SizedBox(height: 6),
            _Input(
              controller: confirmPassword,
              hint: 'Repita a senha',
              obscure: true,
              validator: (v) => v != password.text ? 'Senhas não conferem' : null,
            ),
            const SizedBox(height: 28),
            _PrimaryButton(
              text: 'Continuar',
              onPressed: () {
                if (formKey.currentState?.validate() ?? false) onNext();
              },
            ),
          ],
        ),
      ),
    );
  }
}

// --- Step 2: Dados pessoais ---
class _Step2Personal extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final TextEditingController fullName;
  final TextEditingController cpf;
  final TextEditingController phone;
  final TextEditingController whatsapp;
  final DateTime? birthDate;
  final ValueChanged<DateTime?> onBirthDateChanged;
  final VoidCallback onNext;
  final VoidCallback onPrev;

  const _Step2Personal({
    required this.formKey,
    required this.fullName,
    required this.cpf,
    required this.phone,
    required this.whatsapp,
    required this.birthDate,
    required this.onBirthDateChanged,
    required this.onNext,
    required this.onPrev,
  });

  static int _digitsOnly(String? s) => (s ?? '').replaceAll(RegExp(r'[^0-9]'), '').length;

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Dados pessoais',
      subtitle: 'Nome completo, CPF, telefone e data de nascimento',
      child: Form(
        key: formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _Label('Nome completo *'),
            const SizedBox(height: 6),
            _Input(controller: fullName, hint: 'Mínimo 10 caracteres', validator: (v) => (v == null || v.trim().length < 10) ? 'Mínimo 10 caracteres' : null),
            const SizedBox(height: 18),
            _Label('CPF *'),
            const SizedBox(height: 6),
            _Input(
              controller: cpf,
              hint: '000.000.000-00',
              inputFormatters: [_cpfMask],
              validator: (v) => _digitsOnly(v) != 11 ? 'CPF deve ter 11 dígitos' : null,
            ),
            const SizedBox(height: 18),
            _Label('Telefone *'),
            const SizedBox(height: 6),
            _Input(
              controller: phone,
              hint: '(00) 00000-0000',
              keyboard: TextInputType.phone,
              inputFormatters: [_phoneMask],
              validator: (v) => _digitsOnly(v) != 11 ? 'Telefone deve ter 11 dígitos' : null,
            ),
            const SizedBox(height: 18),
            _Label('WhatsApp (opcional)'),
            const SizedBox(height: 6),
            _Input(
              controller: whatsapp,
              hint: '(00) 00000-0000',
              keyboard: TextInputType.phone,
              inputFormatters: [_phoneMask],
            ),
            const SizedBox(height: 18),
            _Label('Data de nascimento *'),
            const SizedBox(height: 6),
            InkWell(
              onTap: () async {
                final d = await showDatePicker(
                  context: context,
                  initialDate: birthDate ?? DateTime(1990),
                  firstDate: DateTime(1920),
                  lastDate: DateTime.now(),
                );
                if (d != null) onBirthDateChanged(d);
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: Row(
                  children: [
                    Icon(Icons.calendar_today_rounded, size: 20, color: _textSecondary),
                    const SizedBox(width: 12),
                    Text(birthDate != null ? '${birthDate!.day.toString().padLeft(2, '0')}/${birthDate!.month.toString().padLeft(2, '0')}/${birthDate!.year}' : 'Selecione a data', style: TextStyle(color: birthDate != null ? _textPrimary : Colors.grey)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 28),
            _RowButtons(onPrev: onPrev, onNext: onNext, formKey: formKey),
          ],
        ),
      ),
    );
  }
}

// --- Step 3: Endereço ---
class _Step3Address extends StatelessWidget {
  final GlobalKey<FormState> formKey;
  final TextEditingController cep;
  final TextEditingController street;
  final TextEditingController neighborhood;
  final TextEditingController city;
  final TextEditingController state;
  final TextEditingController houseNumber;
  final bool loadingCep;
  final VoidCallback onNext;
  final VoidCallback onPrev;

  const _Step3Address({
    required this.formKey,
    required this.cep,
    required this.street,
    required this.neighborhood,
    required this.city,
    required this.state,
    required this.houseNumber,
    required this.loadingCep,
    required this.onNext,
    required this.onPrev,
  });

  static int _digitsOnly(String? s) => (s ?? '').replaceAll(RegExp(r'[^0-9]'), '').length;

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Endereço',
      subtitle: 'Digite o CEP para buscar automaticamente. Rua, bairro, cidade, estado e número.',
      child: Form(
        key: formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _Label('CEP *'),
            const SizedBox(height: 6),
            Stack(
              alignment: Alignment.centerRight,
              children: [
                _Input(
                  controller: cep,
                  hint: '00000-000',
                  inputFormatters: [_cepMask],
                  validator: (v) => _digitsOnly(v) != 8 ? 'CEP deve ter 8 dígitos' : null,
                ),
                if (loadingCep)
                  const Padding(
                    padding: EdgeInsets.only(right: 16),
                    child: SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(strokeWidth: 2, color: _primary),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 18),
            _Label('Rua *'),
            const SizedBox(height: 6),
            _Input(controller: street, hint: 'Preenchido pelo CEP ou digite', validator: (v) => (v == null || v.trim().isEmpty) ? 'Obrigatório' : null),
            const SizedBox(height: 18),
            _Label('Bairro *'),
            const SizedBox(height: 6),
            _Input(controller: neighborhood, hint: 'Nome do bairro', validator: (v) => (v == null || v.trim().isEmpty) ? 'Obrigatório' : null),
            const SizedBox(height: 18),
            Row(
              children: [
                Expanded(flex: 2, child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [_Label('Cidade *'), const SizedBox(height: 6), _Input(controller: city, hint: 'Cidade', validator: (v) => (v == null || v.trim().isEmpty) ? 'Obrigatório' : null)])),
                const SizedBox(width: 12),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [_Label('UF *'), const SizedBox(height: 6), _Input(controller: state, hint: 'UF', validator: (v) => (v == null || v.trim().isEmpty) ? 'Obrigatório' : null)])),
              ],
            ),
            const SizedBox(height: 18),
            _Label('Número *'),
            const SizedBox(height: 6),
            _Input(controller: houseNumber, hint: '123', validator: (v) => (v == null || v.trim().isEmpty) ? 'Obrigatório' : null),
            const SizedBox(height: 28),
            _RowButtons(onPrev: onPrev, onNext: onNext, formKey: formKey),
          ],
        ),
      ),
    );
  }
}

// --- Step 4: Documentos ---
class _Step4Documents extends StatelessWidget {
  final String documentType;
  final ValueChanged<String> onDocumentTypeChanged;
  final File? userPhoto;
  final File? documentPhoto;
  final ValueChanged<File?> onUserPhotoChanged;
  final ValueChanged<File?> onDocumentPhotoChanged;
  final VoidCallback onNext;
  final VoidCallback onPrev;

  final bool canProceed;

  const _Step4Documents({
    required this.documentType,
    required this.onDocumentTypeChanged,
    required this.userPhoto,
    required this.documentPhoto,
    required this.onUserPhotoChanged,
    required this.onDocumentPhotoChanged,
    required this.onNext,
    required this.onPrev,
    this.canProceed = false,
  });

  Future<File?> _pickImage(BuildContext context) async {
    try {
      final picker = ImagePicker();
      final x = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1200, imageQuality: 85);
      return x != null ? File(x.path) : null;
    } on PlatformException catch (e) {
      if (context.mounted) {
        final isChannelError = e.code == 'channel-error' ||
            (e.message?.contains('Unable to establish connection') ?? false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              isChannelError
                  ? 'Erro ao abrir a galeria. Feche o app completamente e abra de novo (ou execute: flutter clean e flutter run).'
                  : 'Não foi possível abrir a galeria: ${e.message ?? e.code}',
            ),
            behavior: SnackBarBehavior.floating,
            duration: const Duration(seconds: 5),
          ),
        );
      }
      return null;
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erro ao selecionar imagem: $e'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
      return null;
    }
  }

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Documentos',
      subtitle: 'Foto do usuário e documento (RG ou CNH). JPG, PNG ou PDF.',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _Label('Foto do usuário *'),
          const SizedBox(height: 8),
          _PhotoBox(
            file: userPhoto,
            label: 'Toque para enviar foto',
            onTap: () async {
              final f = await _pickImage(context);
              if (f != null) onUserPhotoChanged(f);
            },
          ),
          const SizedBox(height: 20),
          _Label('Tipo de documento *'),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: documentType,
                isExpanded: true,
                items: const [DropdownMenuItem(value: 'rg', child: Text('RG')), DropdownMenuItem(value: 'cnh', child: Text('CNH'))],
                onChanged: (v) => onDocumentTypeChanged(v ?? 'rg'),
              ),
            ),
          ),
          const SizedBox(height: 20),
          _Label('Foto do documento (RG ou CNH) *'),
          const SizedBox(height: 8),
          _PhotoBox(
            file: documentPhoto,
            label: 'Toque para enviar documento',
            onTap: () async {
              final f = await _pickImage(context);
              if (f != null) onDocumentPhotoChanged(f);
            },
          ),
          const SizedBox(height: 28),
          _RowButtons(onPrev: onPrev, onNext: onNext, canProceed: canProceed),
        ],
      ),
    );
  }
}

// --- Step 5: Possui cota? ---
class _Step5Quota extends StatelessWidget {
  final int hasQuota;
  final ValueChanged<int> onHasQuotaChanged;
  final VoidCallback onNext;
  final VoidCallback onPrev;
  final bool canProceed;

  const _Step5Quota({required this.hasQuota, required this.onHasQuotaChanged, required this.onNext, required this.onPrev, this.canProceed = false});

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Cota hoteleira',
      subtitle: 'Você possui cota de multipropriedade ou é gestor?',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _OptionCard(
            selected: hasQuota == 1,
            icon: Icons.check_circle_outline,
            title: 'Sim, tenho cota',
            subtitle: 'Tenho cota e quero usá-la na plataforma',
            onTap: () => onHasQuotaChanged(1),
          ),
          const SizedBox(height: 12),
          _OptionCard(
            selected: hasQuota == 0,
            icon: Icons.cancel_outlined,
            title: 'Não tenho cota',
            subtitle: 'Quero apenas alugar ou comprar',
            onTap: () => onHasQuotaChanged(0),
          ),
          const SizedBox(height: 12),
          _OptionCard(
            selected: hasQuota == 2,
            icon: Icons.badge_outlined,
            title: 'Não, mas tenho autorização para ser gestor',
            subtitle: 'Tenho autorização para gerenciar cota de terceiros',
            onTap: () => onHasQuotaChanged(2),
          ),
          const SizedBox(height: 28),
          _RowButtons(onPrev: onPrev, onNext: onNext, canProceed: canProceed),
        ],
      ),
    );
  }
}

// --- Step 5b: Informações da cota ---
class _StepQuotaInfo extends StatefulWidget {
  final GlobalKey<FormState> formKey;
  final int hasQuota;
  final int? ownerHotelId;
  final ValueChanged<int?> onOwnerHotelIdChanged;
  final bool? hotelInOperation;
  final ValueChanged<bool?> onHotelInOperationChanged;
  final File? quotaContractFile;
  final ValueChanged<File?> onQuotaContractFileChanged;
  final File? hospitalityAuthorizationTermFile;
  final ValueChanged<File?> onHospitalityAuthorizationTermFileChanged;
  final String? quotaStatus;
  final ValueChanged<String?> onQuotaStatusChanged;
  final DateTime? quotaPaymentDeadline;
  final ValueChanged<DateTime?> onQuotaPaymentDeadlineChanged;
  final int? ownerQuotaRooms;
  final ValueChanged<int?> onOwnerQuotaRoomsChanged;
  final Map<int, RoomConfig> ownerRoomConfig;
  final void Function(int roomIndex, RoomConfig config) onOwnerRoomConfigChanged;
  final TextEditingController ownerQuotaSize;
  final Map<String, bool?> quotaAmenities;
  final void Function(String key, bool? value) onQuotaAmenityChanged;
  final String ownerQuotaSeasonality;
  final ValueChanged<String> onOwnerQuotaSeasonalityChanged;
  final String ownerQuotaType;
  final ValueChanged<String> onOwnerQuotaTypeChanged;
  final bool ownerQuotaBreakfast;
  final ValueChanged<bool> onOwnerQuotaBreakfastChanged;
  final int? ownerQuotaWeeksCount;
  final ValueChanged<int?> onOwnerQuotaWeeksCountChanged;
  final Map<int, WeekBlockData> ownerWeekBlocks;
  final void Function(int weekIndex, WeekBlockData data) onOwnerWeekBlockChanged;
  final List<String> allowedUses;
  final ValueChanged<List<String>> onAllowedUsesChanged;
  final TextEditingController ownerQuotaObservations;
  final VoidCallback onNext;
  final VoidCallback onPrev;

  const _StepQuotaInfo({
    required this.formKey,
    required this.hasQuota,
    required this.ownerHotelId,
    required this.onOwnerHotelIdChanged,
    required this.hotelInOperation,
    required this.onHotelInOperationChanged,
    required this.quotaContractFile,
    required this.onQuotaContractFileChanged,
    required this.hospitalityAuthorizationTermFile,
    required this.onHospitalityAuthorizationTermFileChanged,
    required this.quotaStatus,
    required this.onQuotaStatusChanged,
    required this.quotaPaymentDeadline,
    required this.onQuotaPaymentDeadlineChanged,
    required this.ownerQuotaRooms,
    required this.onOwnerQuotaRoomsChanged,
    required this.ownerRoomConfig,
    required this.onOwnerRoomConfigChanged,
    required this.ownerQuotaSize,
    required this.quotaAmenities,
    required this.onQuotaAmenityChanged,
    required this.ownerQuotaSeasonality,
    required this.onOwnerQuotaSeasonalityChanged,
    required this.ownerQuotaType,
    required this.onOwnerQuotaTypeChanged,
    required this.ownerQuotaBreakfast,
    required this.onOwnerQuotaBreakfastChanged,
    required this.ownerQuotaWeeksCount,
    required this.onOwnerQuotaWeeksCountChanged,
    required this.ownerWeekBlocks,
    required this.onOwnerWeekBlockChanged,
    required this.allowedUses,
    required this.onAllowedUsesChanged,
    required this.ownerQuotaObservations,
    required this.onNext,
    required this.onPrev,
  });

  @override
  State<_StepQuotaInfo> createState() => _StepQuotaInfoState();
}

class _StepQuotaInfoState extends State<_StepQuotaInfo> {
  List<Map<String, dynamic>> _hotels = [];
  bool _loadingHotels = true;

  @override
  void initState() {
    super.initState();
    _loadHotels();
  }

  Future<void> _pickQuotaDocument(BuildContext context, ValueChanged<File?> onFile) async {
    try {
      final picker = ImagePicker();
      final x = await picker.pickImage(source: ImageSource.gallery, maxWidth: 1600, imageQuality: 85);
      if (!mounted) return;
      if (x != null) {
        onFile(File(x.path));
      }
    } on PlatformException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(e.code == 'channel-error' || (e.message?.contains('Unable to establish connection') ?? false)
                ? 'Erro ao abrir a galeria. Feche o app e abra novamente (ou execute: flutter clean e flutter run).'
                : 'Erro: ${e.message ?? e.code}'),
            behavior: SnackBarBehavior.floating,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erro ao selecionar arquivo: $e'), behavior: SnackBarBehavior.floating),
        );
      }
    }
  }

  Future<void> _loadHotels() async {
    try {
      final api = ApiClient();
      final res = await api.getJson('/hotels', auth: false);
      if (!mounted) return;
      final data = res['data'];
      if (data is List) {
        setState(() {
          _hotels = data.map((e) => Map<String, dynamic>.from(e as Map)).toList();
          _loadingHotels = false;
        });
      } else {
        setState(() => _loadingHotels = false);
      }
    } catch (_) {
      if (mounted) setState(() => _loadingHotels = false);
    }
  }

  String _selectedHotelLabel() {
    if (widget.ownerHotelId == null) return 'Buscar e selecionar hotel';
    try {
      final h = _hotels.firstWhere((e) => e['id'] == widget.ownerHotelId);
      final name = h['name'] as String? ?? '';
      final city = h['city'] as String?;
      final state = h['state'] as String?;
      return '$name${city != null || state != null ? ' - ${city ?? ''}/${state ?? ''}' : ''}';
    } catch (_) {
      return 'Buscar e selecionar hotel';
    }
  }

  void _showHotelPicker(BuildContext context) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _HotelSearchSheet(
        selectedId: widget.ownerHotelId,
        onSelect: (id) {
          widget.onOwnerHotelIdChanged(id);
          Navigator.of(ctx).pop();
        },
      ),
    );
  }

  bool _weekBlocksValid() {
    final n = widget.ownerQuotaWeeksCount;
    if (n == null || n == 0) return true;
    for (int i = 1; i <= n; i++) {
      final b = widget.ownerWeekBlocks[i];
      if (b == null || !b.isValid) return false;
    }
    return true;
  }

  bool _roomsConfigValid() {
    final n = widget.ownerQuotaRooms;
    if (n == null || n == 0) return true;
    for (int i = 1; i <= n; i++) {
      final c = widget.ownerRoomConfig[i];
      if (c == null || !c.isValid) return false;
    }
    return true;
  }

  static const List<MapEntry<String, String>> _amenityLabels = [
    MapEntry('varanda', 'Varanda'),
    MapEntry('cozinha_completa', 'Cozinha Completa'),
    MapEntry('vista_mar', 'Vista Mar'),
    MapEntry('jacuzzi', 'Jacuzzi'),
    MapEntry('spa', 'Spa'),
    MapEntry('piscina', 'Piscina'),
    MapEntry('academia', 'Academia'),
    MapEntry('lareira', 'Lareira'),
    MapEntry('adega', 'Adega'),
    MapEntry('area_kids', 'Área Kids'),
    MapEntry('area_trabalho', 'Área de trabalho'),
    MapEntry('wifi', 'Wifi'),
    MapEntry('estacionamento_gratuito', 'Estacionamento Gratuito'),
  ];

  List<Widget> _quotaAmenitiesFields() {
    final list = <Widget>[];
    for (final e in _amenityLabels) {
      list.add(_Label(e.value));
      list.add(const SizedBox(height: 6));
      list.add(
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
          decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<bool?>(
              value: widget.quotaAmenities[e.key],
              isExpanded: true,
              hint: const Text('Selecione'),
              items: const [
                DropdownMenuItem<bool?>(value: null, child: Text('Selecione')),
                DropdownMenuItem<bool?>(value: true, child: Text('Sim')),
                DropdownMenuItem<bool?>(value: false, child: Text('Não')),
              ],
              onChanged: (v) => widget.onQuotaAmenityChanged(e.key, v),
            ),
          ),
        ),
      );
      list.add(const SizedBox(height: 14));
    }
    return list;
  }

  Widget _quotaRoomsConfigSection() {
    final n = widget.ownerQuotaRooms!;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text('Configuração dos Quartos', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: _textPrimary)),
        const SizedBox(height: 12),
        ...List.generate(n, (i) {
          final roomNumber = i + 1;
          return Padding(
            padding: const EdgeInsets.only(bottom: 20),
            child: _RoomBlock(
              roomNumber: roomNumber,
              config: widget.ownerRoomConfig[roomNumber] ?? RoomConfig(),
              onChanged: (c) => widget.onOwnerRoomConfigChanged(roomNumber, c),
            ),
          );
        }),
        const SizedBox(height: 8),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(color: const Color(0xFFFFF3CD), borderRadius: BorderRadius.circular(10), border: Border.all(color: const Color(0xFFFFC107))),
          child: Text('Aviso: O número de pessoas deve ser igual ou menor que o número de leitos', style: TextStyle(fontSize: 13, color: _textPrimary)),
        ),
        const SizedBox(height: 14),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          decoration: BoxDecoration(color: _primaryLight, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Leitos por tipo:', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _textPrimary)),
              const SizedBox(height: 6),
              Text('Cama de casal: 2 Leitos', style: TextStyle(fontSize: 13, color: _textSecondary)),
              Text('Cama de solteiro: 1 Leito', style: TextStyle(fontSize: 13, color: _textSecondary)),
              Text('Sofa-cama: 2 Leitos', style: TextStyle(fontSize: 13, color: _textSecondary)),
              Text('Beliche: 2 Leitos', style: TextStyle(fontSize: 13, color: _textSecondary)),
            ],
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Informações da cota',
      subtitle: 'Preencha os dados da sua cota conforme o contrato',
      child: Form(
        key: widget.formKey,
        child: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  Icon(Icons.business_rounded, size: 20, color: _primary),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'O Hotel onde você é(será) Gestor da Cota está em funcionamento? *',
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: _textPrimary),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: _OptionCard(
                      selected: widget.hotelInOperation == true,
                      icon: Icons.check_circle_outline,
                      title: 'Sim',
                      subtitle: 'O hotel está em pleno funcionamento',
                      onTap: () => widget.onHotelInOperationChanged(true),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _OptionCard(
                      selected: widget.hotelInOperation == false,
                      icon: Icons.cancel_outlined,
                      title: 'Não',
                      subtitle: 'O hotel não está em funcionamento',
                      onTap: () => widget.onHotelInOperationChanged(false),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),
              _Label('Documento de confirmação da Posse da Cota *'),
              const SizedBox(height: 6),
              _QuotaFileBox(
                file: widget.quotaContractFile,
                label: 'Escolher arquivo',
                hint: 'Toque para enviar foto/imagem (JPG ou PNG) da primeira folha do contrato com nome do hotel, titular, CPF e detalhes da cota.',
                onTap: () => _pickQuotaDocument(context, widget.onQuotaContractFileChanged),
              ),
              const SizedBox(height: 18),
              _Label('Termo de Autorização de Hospedagem para Terceiros'),
              const SizedBox(height: 6),
              _QuotaFileBox(
                file: widget.hospitalityAuthorizationTermFile,
                label: 'Escolher arquivo',
                hint: 'Toque para enviar foto/imagem (JPG ou PNG) do termo oficial do hotel. Opcional.',
                onTap: () => _pickQuotaDocument(context, widget.onHospitalityAuthorizationTermFileChanged),
              ),
              const SizedBox(height: 18),
              _Label('Status da Cota *'),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: widget.quotaStatus,
                    isExpanded: true,
                    hint: const Text('Selecione o status da cota'),
                    items: const [DropdownMenuItem(value: 'paid', child: Text('Quitada')), DropdownMenuItem(value: 'unpaid', child: Text('Não quitada'))],
                    onChanged: (v) => widget.onQuotaStatusChanged(v),
                  ),
                ),
              ),
              if (widget.quotaStatus == 'unpaid') ...[
                const SizedBox(height: 18),
                _Label('Prazo para Quitação'),
                const SizedBox(height: 6),
                InkWell(
                  onTap: () async {
                    final d = await showDatePicker(
                      context: context,
                      initialDate: widget.quotaPaymentDeadline ?? DateTime.now().add(const Duration(days: 30)),
                      firstDate: DateTime.now(),
                      lastDate: DateTime.now().add(const Duration(days: 365 * 2)),
                    );
                    if (d != null) widget.onQuotaPaymentDeadlineChanged(d);
                  },
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                    decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                    child: Row(
                      children: [
                        Icon(Icons.calendar_today_rounded, size: 20, color: _textSecondary),
                        const SizedBox(width: 12),
                        Text(
                          widget.quotaPaymentDeadline != null
                              ? '${widget.quotaPaymentDeadline!.day.toString().padLeft(2, '0')}/${widget.quotaPaymentDeadline!.month.toString().padLeft(2, '0')}/${widget.quotaPaymentDeadline!.year}'
                              : 'dd/mm/aaaa',
                          style: TextStyle(color: widget.quotaPaymentDeadline != null ? _textPrimary : Colors.grey, fontSize: 15),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Text('Informe o prazo para quitação da Cota', style: TextStyle(fontSize: 12, color: _textSecondary)),
              ],
              const SizedBox(height: 18),
              _Label('Usos permitidos *'),
              const SizedBox(height: 10),
              _AllowedUsesChips(selected: widget.allowedUses, onChanged: widget.onAllowedUsesChanged),
              const SizedBox(height: 18),
              _Label('Hotel *'),
              const SizedBox(height: 6),
              _loadingHotels
                  ? const Center(child: Padding(padding: EdgeInsets.all(24), child: CircularProgressIndicator(color: _primary)))
                  : InkWell(
                      onTap: () => _showHotelPicker(context),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                        decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                        child: Row(
                          children: [
                            Icon(Icons.hotel_rounded, size: 22, color: _textSecondary),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                _selectedHotelLabel(),
                                style: TextStyle(color: widget.ownerHotelId != null ? _textPrimary : Colors.grey, fontSize: 15),
                              ),
                            ),
                            Icon(Icons.search_rounded, size: 22, color: _textSecondary),
                          ],
                        ),
                      ),
                    ),
              const SizedBox(height: 18),
              _Label('Quartos *'),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    value: widget.ownerQuotaRooms,
                    isExpanded: true,
                    hint: const Text('Selecione a quantidade'),
                    items: [1, 2, 3].map((n) => DropdownMenuItem(value: n, child: Text('$n Quarto${n > 1 ? 's' : ''}'))).toList(),
                    onChanged: (v) => widget.onOwnerQuotaRoomsChanged(v),
                  ),
                ),
              ),
              if (widget.ownerQuotaRooms != null && widget.ownerQuotaRooms! > 0) ...[
                const SizedBox(height: 20),
                _quotaRoomsConfigSection(),
              ],
              const SizedBox(height: 18),
              _Label('Tamanho (m²) *'),
              const SizedBox(height: 6),
              _Input(controller: widget.ownerQuotaSize, hint: 'Ex: 45, 50-60', validator: (v) => (v == null || v.trim().isEmpty) ? 'Obrigatório' : null),
              const SizedBox(height: 18),
              ..._quotaAmenitiesFields(),
              const SizedBox(height: 18),
              _Label('Sazonalidade *'),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: widget.ownerQuotaSeasonality,
                    isExpanded: true,
                    items: const [
                      DropdownMenuItem(value: 'baixa', child: Text('Baixa')),
                      DropdownMenuItem(value: 'media', child: Text('Média')),
                      DropdownMenuItem(value: 'alta', child: Text('Alta')),
                      DropdownMenuItem(value: 'pico', child: Text('Altíssima')),
                    ],
                    onChanged: (v) => widget.onOwnerQuotaSeasonalityChanged(v ?? 'media'),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              _Label('Tipo de cota *'),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: widget.ownerQuotaType,
                    isExpanded: true,
                    items: const [
                      DropdownMenuItem(value: 'fixa', child: Text('Fixa')),
                      DropdownMenuItem(value: 'flexivel', child: Text('Flexível')),
                      DropdownMenuItem(value: 'fix_flexivel', child: Text('Fixa + Flexível')),
                    ],
                    onChanged: (v) => widget.onOwnerQuotaTypeChanged(v ?? 'fixa'),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              _Label('Café da manhã incluso *'),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<bool>(
                    value: widget.ownerQuotaBreakfast,
                    isExpanded: true,
                    items: const [DropdownMenuItem(value: true, child: Text('Sim')), DropdownMenuItem(value: false, child: Text('Não'))],
                    onChanged: (v) => widget.onOwnerQuotaBreakfastChanged(v ?? true),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              _Label('Quantas semanas você possui nesta cota? *'),
              const SizedBox(height: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    value: widget.ownerQuotaWeeksCount,
                    isExpanded: true,
                    hint: const Text('Selecione'),
                    items: List.generate(6, (i) => i + 1).map((n) => DropdownMenuItem(value: n, child: Text('$n'))).toList(),
                    onChanged: (v) => widget.onOwnerQuotaWeeksCountChanged(v),
                  ),
                ),
              ),
              if (widget.ownerQuotaWeeksCount != null && widget.ownerQuotaWeeksCount! >= 1) ...[
                const SizedBox(height: 18),
                ...List.generate(widget.ownerQuotaWeeksCount!, (i) {
                  final n = i + 1;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 20),
                    child: _WeekBlock(
                      weekNumber: n,
                      data: widget.ownerWeekBlocks[n] ?? WeekBlockData(),
                      onChanged: (d) => widget.onOwnerWeekBlockChanged(n, d),
                      onPickProof: () => _pickQuotaDocument(context, (f) {
                        final d = widget.ownerWeekBlocks[n] ?? WeekBlockData();
                        widget.onOwnerWeekBlockChanged(n, d.copyWith(proofFile: f));
                      }),
                    ),
                  );
                }),
              ],
              const SizedBox(height: 18),
              _Label('Observações (opcional)'),
              const SizedBox(height: 6),
              _Input(controller: widget.ownerQuotaObservations, hint: 'Detalhes da cota'),
              const SizedBox(height: 28),
              _RowButtons(
                onPrev: widget.onPrev,
                onNext: widget.onNext,
                formKey: widget.formKey,
                canProceed: widget.hotelInOperation != null &&
                    widget.ownerHotelId != null &&
                    widget.quotaContractFile != null &&
                    widget.allowedUses.isNotEmpty &&
                    widget.ownerQuotaWeeksCount != null &&
                    _roomsConfigValid() &&
                    _weekBlocksValid(),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _HotelSearchSheet extends StatefulWidget {
  final int? selectedId;
  final ValueChanged<int?> onSelect;

  const _HotelSearchSheet({required this.selectedId, required this.onSelect});

  @override
  State<_HotelSearchSheet> createState() => _HotelSearchSheetState();
}

class _HotelSearchSheetState extends State<_HotelSearchSheet> {
  final _searchController = TextEditingController();
  List<Map<String, dynamic>> _hotels = [];
  bool _loading = true;
  String? _error;
  Timer? _debounceTimer;

  @override
  void initState() {
    super.initState();
    _fetchHotels(null);
    _searchController.addListener(_onSearchChanged);
  }

  void _onSearchChanged() {
    if (_debounceTimer?.isActive ?? false) _debounceTimer!.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 400), () {
      final q = _searchController.text.trim();
      _fetchHotels(q.isEmpty ? null : q);
    });
  }

  Future<void> _fetchHotels(String? searchQuery) async {
    if (!mounted) return;
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final api = ApiClient();
      final params = searchQuery != null && searchQuery.isNotEmpty
          ? {'search': searchQuery}
          : null;
      final res = await api.getJson('/hotels', params: params, auth: false);
      if (!mounted) return;
      final data = res['data'];
      if (data is List) {
        setState(() {
          _hotels = data.map((e) => Map<String, dynamic>.from(e as Map)).toList();
          _loading = false;
          _error = null;
        });
      } else {
        setState(() {
          _hotels = [];
          _loading = false;
          _error = 'Resposta inválida da API';
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _hotels = [];
          _loading = false;
          _error = 'Erro ao carregar hotéis. Verifique a conexão.';
        });
      }
    }
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _searchController.removeListener(_onSearchChanged);
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      initialChildSize: 0.6,
      minChildSize: 0.3,
      maxChildSize: 0.95,
      builder: (_, scrollController) => Container(
        decoration: const BoxDecoration(color: _cardBg, borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
        child: Column(
          children: [
            const SizedBox(height: 12),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const SizedBox(width: 40),
                  Text('Buscar hotel', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600, color: _textPrimary)),
                  IconButton(
                    icon: Icon(Icons.close_rounded, color: _textSecondary, size: 28),
                    onPressed: () => Navigator.of(context).pop(),
                    tooltip: 'Fechar',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: TextField(
                controller: _searchController,
                decoration: InputDecoration(
                  hintText: 'Digite o nome, cidade ou estado',
                  prefixIcon: const Icon(Icons.search_rounded, color: _textSecondary),
                  filled: true,
                  fillColor: _inputBg,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                ),
              ),
            ),
            const SizedBox(height: 8),
            Expanded(
              child: _loading
                  ? const Center(
                      child: Padding(
                        padding: EdgeInsets.all(32),
                        child: CircularProgressIndicator(color: _primary),
                      ),
                    )
                  : _error != null
                      ? Center(
                          child: Padding(
                            padding: const EdgeInsets.all(24),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.error_outline_rounded, size: 48, color: Colors.grey[400]),
                                const SizedBox(height: 12),
                                Text(_error!, textAlign: TextAlign.center, style: TextStyle(fontSize: 14, color: _textSecondary)),
                              ],
                            ),
                          ),
                        )
                      : _hotels.isEmpty
                          ? Center(
                              child: Padding(
                                padding: const EdgeInsets.all(24),
                                child: Text(
                                  _searchController.text.trim().isEmpty
                                      ? 'Nenhum hotel cadastrado'
                                      : 'Nenhum hotel encontrado para "${_searchController.text.trim()}"',
                                  textAlign: TextAlign.center,
                                  style: TextStyle(fontSize: 14, color: _textSecondary),
                                ),
                              ),
                            )
                          : ListView.builder(
                              controller: scrollController,
                              itemCount: _hotels.length,
                              itemBuilder: (_, i) {
                                final h = _hotels[i];
                                final id = h['id'] as int?;
                                final name = h['name'] as String? ?? '';
                                final city = h['city'] as String?;
                                final state = h['state'] as String?;
                                final selected = id == widget.selectedId;
                                return ListTile(
                                  title: Text(name, style: TextStyle(fontWeight: selected ? FontWeight.w600 : null, color: selected ? _primary : _textPrimary)),
                                  subtitle: (city != null || state != null) ? Text('${city ?? ''} / ${state ?? ''}', style: TextStyle(fontSize: 12, color: _textSecondary)) : null,
                                  trailing: selected ? Icon(Icons.check_circle, color: _primary, size: 24) : null,
                                  onTap: () => widget.onSelect(id),
                                );
                              },
                            ),
            ),
          ],
        ),
      ),
    );
  }
}

class _WeekBlock extends StatelessWidget {
  final int weekNumber;
  final WeekBlockData data;
  final ValueChanged<WeekBlockData> onChanged;
  final VoidCallback onPickProof;

  const _WeekBlock({
    required this.weekNumber,
    required this.data,
    required this.onChanged,
    required this.onPickProof,
  });

  static List<String> get _dayOptions => List.generate(31, (i) => (i + 1).toString().padLeft(2, '0'));
  static List<String> get _monthOptions => List.generate(12, (i) => (i + 1).toString().padLeft(2, '0'));
  static List<String> get _yearOptions {
    final y = DateTime.now().year;
    return [y.toString(), (y + 1).toString()];
  }

  @override
  Widget build(BuildContext context) {
    final showFields = data.isAuthorized;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _cardBg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Icon(Icons.calendar_month_rounded, size: 22, color: _primary),
              const SizedBox(width: 8),
              Text('Semana $weekNumber', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: _primary)),
            ],
          ),
          const SizedBox(height: 14),
          Text('Autoriza o aluguel e/ou troca e ou compra/venda desta semana? *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _textPrimary)),
          const SizedBox(height: 6),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
            child: DropdownButtonHideUnderline(
              child: DropdownButton<String>(
                value: data.authorize != null && data.authorize!.isNotEmpty ? data.authorize! : '',
                isExpanded: true,
                hint: const Text('Selecione'),
                items: const [
                  DropdownMenuItem(value: '', child: Text('Selecione')),
                  DropdownMenuItem(value: 'yes', child: Text('Sim')),
                  DropdownMenuItem(value: 'no', child: Text('Não')),
                ],
                onChanged: (v) => onChanged(data.copyWith(authorize: (v == null || v.isEmpty) ? '' : v)),
              ),
            ),
          ),
          if (showFields) ...[
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _buildDropdown('Dia de Início *', data.startDay, _dayOptions, (v) {
                    final end = _computeEndDay(v, data.month, data.year);
                    onChanged(data.copyWith(startDay: v, endDay: end));
                  }),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Dia de Término', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _textPrimary)),
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                        decoration: BoxDecoration(color: _inputBg.withOpacity(0.7), borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                        child: Text(data.endDay ?? '—', style: TextStyle(fontSize: 15, color: _textSecondary)),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: _buildDropdown('Mês *', data.month, _monthOptions, (v) {
                    final end = _computeEndDay(data.startDay, v, data.year);
                    onChanged(data.copyWith(month: v, endDay: end));
                  }),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildDropdown('Ano *', data.year, _yearOptions, (v) {
                    final end = _computeEndDay(data.startDay, data.month, v);
                    onChanged(data.copyWith(year: v, endDay: end));
                  }),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Text('Comprovação de Período de Uso *', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _textPrimary)),
            const SizedBox(height: 6),
            GestureDetector(
              behavior: HitTestBehavior.opaque,
              onTap: onPickProof,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
                child: Row(
                  children: [
                    Icon(Icons.attach_file, size: 22, color: _textSecondary),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        data.proofFile != null ? data.proofFile!.path.split(RegExp(r'[/\\]')).last : 'Nenhum arquivo escolhido',
                        style: TextStyle(fontSize: 14, color: data.proofFile != null ? _textPrimary : Colors.grey),
                      ),
                    ),
                    Text('Escolher arquivo', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: _primary)),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 6),
            Text('Uma foto do documento oficial de distribuição das semanas para uso no seu hotel. JPG ou PNG.', style: TextStyle(fontSize: 12, color: _textSecondary)),
          ],
        ],
      ),
    );
  }

  Widget _buildDropdown(String label, String? value, List<String> options, ValueChanged<String?> onChanged) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _textPrimary)),
        const SizedBox(height: 4),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: value != null && value.isNotEmpty ? value : null,
              isExpanded: true,
              hint: const Text('Selecione'),
              items: options.map((v) => DropdownMenuItem(value: v, child: Text(v))).toList(),
              onChanged: onChanged,
            ),
          ),
        ),
      ],
    );
  }
}

class _RoomBlock extends StatelessWidget {
  final int roomNumber;
  final RoomConfig config;
  final ValueChanged<RoomConfig> onChanged;

  const _RoomBlock({required this.roomNumber, required this.config, required this.onChanged});

  static Widget _dropdown<T>({required String label, required T? value, required List<T> values, required String Function(T) text, required ValueChanged<T?> onChanged}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(label, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: _textPrimary)),
        const SizedBox(height: 4),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
          decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(10), border: Border.all(color: _border)),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<T>(
              value: value,
              isExpanded: true,
              hint: const Text('Selecione'),
              items: values.map((v) => DropdownMenuItem(value: v, child: Text(text(v)))).toList(),
              onChanged: onChanged,
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: _cardBg, borderRadius: BorderRadius.circular(12), border: Border.all(color: _border)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Icon(Icons.bed_rounded, size: 22, color: _primary),
              const SizedBox(width: 8),
              Text('Quarto $roomNumber', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: _primary)),
              const Spacer(),
              Expanded(
                child: _dropdown<int>(
                  label: 'Suíte',
                  value: config.suite >= 0 ? config.suite : null,
                  values: const [0, 1],
                  text: (v) => v == 0 ? 'Não' : 'Sim',
                  onChanged: (v) { if (v != null) onChanged(config.copyWith(suite: v)); },
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _dropdown<int>(
                  label: 'Cama de Casal',
                  value: config.doubleBed,
                  values: const [0, 1, 2],
                  text: (v) => '$v',
                  onChanged: (v) => onChanged(config.copyWith(doubleBed: v ?? 0)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _dropdown<int>(
                  label: 'Cama de Solteiro',
                  value: config.singleBed,
                  values: const [0, 1, 2, 3, 4, 5],
                  text: (v) => '$v',
                  onChanged: (v) => onChanged(config.copyWith(singleBed: v ?? 0)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _dropdown<int>(
                  label: 'Sofá Cama',
                  value: config.sofaBed,
                  values: const [0, 1, 2],
                  text: (v) => '$v',
                  onChanged: (v) => onChanged(config.copyWith(sofaBed: v ?? 0)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _dropdown<int>(
                  label: 'Beliche',
                  value: config.bunkBed,
                  values: const [0, 1, 2, 3, 4, 5],
                  text: (v) => '$v',
                  onChanged: (v) => onChanged(config.copyWith(bunkBed: v ?? 0)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _dropdown<int>(
                  label: 'Pessoas',
                  value: config.people >= 1 ? config.people : null,
                  values: List.generate(10, (i) => i + 1),
                  text: (v) => v == 1 ? '1 Pessoa' : '$v Pessoas',
                  onChanged: (v) { if (v != null) onChanged(config.copyWith(people: v)); },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _QuotaFileBox extends StatelessWidget {
  final File? file;
  final String label;
  final String hint;
  final VoidCallback onTap;

  const _QuotaFileBox({this.file, required this.label, required this.hint, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        GestureDetector(
          behavior: HitTestBehavior.opaque,
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              color: _inputBg,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: _border),
            ),
            child: Row(
              children: [
                Icon(Icons.attach_file, size: 22, color: _textSecondary),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    file != null ? file!.path.split(RegExp(r'[/\\]')).last : 'Nenhum arquivo escolhido',
                    style: TextStyle(fontSize: 14, color: file != null ? _textPrimary : Colors.grey),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                Text(label, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: _primary)),
              ],
            ),
          ),
        ),
        const SizedBox(height: 6),
        Text(hint, style: TextStyle(fontSize: 11, color: _textSecondary, height: 1.3)),
      ],
    );
  }
}

class _AllowedUsesChips extends StatelessWidget {
  final List<String> selected;
  final ValueChanged<List<String>> onChanged;

  const _AllowedUsesChips({required this.selected, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    final options = ['rent', 'exchange', 'sell', 'buy'];
    final labels = {'rent': 'Alugar', 'exchange': 'Trocar', 'sell': 'Vender', 'buy': 'Comprar'};
    return Wrap(
      spacing: 12,
      runSpacing: 12,
      children: options.map((v) {
        final isSelected = selected.contains(v);
        return FilterChip(
          label: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
            child: Text(
              labels[v] ?? v,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: isSelected ? _primary : _textPrimary,
              ),
            ),
          ),
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
          selected: isSelected,
          onSelected: (s) {
            if (s) {
              onChanged([...selected, v]);
            } else {
              onChanged(selected.where((x) => x != v).toList());
            }
          },
          selectedColor: _primaryLight,
          checkmarkColor: _primary,
          side: BorderSide(color: isSelected ? _primary : _border, width: isSelected ? 2 : 1),
          showCheckmark: true,
        );
      }).toList(),
    );
  }
}

// --- Fracionamento (uma tela por semana) ---
class _StepFraction extends StatelessWidget {
  final int weekNumber;
  final String profileType;
  final String fractionType;
  final ValueChanged<String> onFractionTypeChanged;
  final List<String> periodActions;
  final ValueChanged<List<String>> onPeriodActionsChanged;
  final VoidCallback onNext;
  final VoidCallback onPrev;

  const _StepFraction({
    required this.weekNumber,
    required this.profileType,
    required this.fractionType,
    required this.onFractionTypeChanged,
    required this.periodActions,
    required this.onPeriodActionsChanged,
    required this.onNext,
    required this.onPrev,
  });

  static const _inteligenteOptions = ['7', '3_4', '4_3'];
  static const _sabioOptions = ['7', '2_2_3', '3_4', '2_5', '3_2_2', '4_3'];
  static const _fractionLabels = {
    '7': 'Integral (7 dias)',
    '3_4': '3 + 4 dias',
    '4_3': '4 + 3 dias',
    '2_2_3': '2 + 2 + 3 dias',
    '2_5': '2 + 5 dias',
    '3_2_2': '3 + 2 + 2 dias',
  };
  static const _periodCounts = {'7': 1, '3_4': 2, '4_3': 2, '2_2_3': 3, '2_5': 2, '3_2_2': 3};

  @override
  Widget build(BuildContext context) {
    final options = profileType == 'sabio' ? _sabioOptions : _inteligenteOptions;
    final periodCount = _periodCounts[fractionType] ?? 1;
    final actions = List<String>.from(periodActions);
    while (actions.length < periodCount) {
      actions.add('rent');
    }
    if (actions.length > periodCount) {
      actions.length = periodCount;
    }

    return _StepCard(
      title: 'Fracionamento - Semana $weekNumber',
      subtitle: 'Escolha como dividir os 7 dias e se habilita cada período para aluguel e/ou troca',
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _Label('Tipo de fracionamento *'),
            const SizedBox(height: 8),
            ...options.map((opt) => Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: InkWell(
                    onTap: () => onFractionTypeChanged(opt),
                    borderRadius: BorderRadius.circular(12),
                    child: Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                        color: fractionType == opt ? _primaryLight.withValues(alpha: 0.7) : _inputBg,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: fractionType == opt ? _primary : _border, width: fractionType == opt ? 2 : 1),
                      ),
                      child: Row(
                        children: [
                          Icon(fractionType == opt ? Icons.radio_button_checked : Icons.radio_button_off, color: fractionType == opt ? _primary : _textSecondary, size: 24),
                          const SizedBox(width: 12),
                          Text(_fractionLabels[opt] ?? opt, style: TextStyle(fontWeight: FontWeight.w600, color: fractionType == opt ? _primary : _textPrimary)),
                        ],
                      ),
                    ),
                  ),
                )),
            const SizedBox(height: 20),
            _Label('Ação por período'),
            const SizedBox(height: 8),
            ...List.generate(periodCount, (i) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Row(
                  children: [
                    Text('Período ${i + 1}:', style: TextStyle(fontSize: 13, color: _textSecondary)),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(color: _inputBg, borderRadius: BorderRadius.circular(10), border: Border.all(color: _border)),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: actions[i],
                            isExpanded: true,
                            items: const [
                              DropdownMenuItem(value: 'rent', child: Text('Aluguel')),
                              DropdownMenuItem(value: 'exchange', child: Text('Troca')),
                              DropdownMenuItem(value: 'rent_exchange', child: Text('Aluguel e Troca')),
                            ],
                            onChanged: (v) {
                              actions[i] = v ?? 'rent';
                              onPeriodActionsChanged(List.from(actions));
                            },
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              );
            }),
            const SizedBox(height: 28),
            _RowButtons(onPrev: onPrev, onNext: onNext),
          ],
        ),
      ),
    );
  }
}

// Textos para o popup "Informações do perfil"
const _profileInfoCurioso = 'Perfil Curioso\n\n'
    'Ideal para quem quer conhecer e explorar a plataforma Cota Brasilis.\n\n'
    '• Acesso para visualizar cotas e ofertas\n'
    '• Possibilidade de registrar sua cota sem fracionamento\n'
    '• Sem taxa de sucesso para fracionamento (não aplicável)\n\n'
    'Você pode evoluir para os perfis Inteligente ou Sábio depois.';

const _profileInfoInteligente = 'Perfil Inteligente\n\n'
    'Para quem possui uma semana de cota no hotel.\n\n'
    '• Uma semana de cota no mesmo hotel\n'
    '• Pode fracionar a semana em períodos (ex.: 3+4 ou 4+3 dias)\n'
    '• Para cada período pode habilitar: Aluguel, Troca ou Aluguel e Troca\n'
    '• Taxa de sucesso aplicável conforme regulamento\n\n'
    'Ideal para maximizar o uso da sua semana.';

const _profileInfoSabio = 'Perfil Sábio\n\n'
    'Para quem possui duas ou mais semanas de cota.\n\n'
    '• Duas a seis semanas no mesmo hotel\n'
    '• Pode fracionar cada semana em diferentes combinações (ex.: 2+2+3, 2+5, 3+2+2 dias)\n'
    '• Para cada período pode habilitar: Aluguel, Troca ou Aluguel e Troca\n'
    '• Maior flexibilidade e opções de fracionamento\n'
    '• Taxa de sucesso aplicável conforme regulamento\n\n'
    'Ideal para quem quer o máximo de flexibilidade na gestão das semanas.';

void _showProfileInfoDialog(BuildContext context, String title, String body) {
  showDialog(
    context: context,
    builder: (ctx) => AlertDialog(
      title: Text(title),
      content: SingleChildScrollView(
        child: Text(body, style: TextStyle(fontSize: 14, height: 1.4, color: _textPrimary)),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(ctx).pop(),
          child: const Text('Fechar', style: TextStyle(color: _primary, fontWeight: FontWeight.w600)),
        ),
      ],
    ),
  );
}

// --- Step 6: Perfil ---
class _Step6Profile extends StatelessWidget {
  final String? profileType;
  final ValueChanged<String?> onProfileTypeChanged;
  final VoidCallback onNext;
  final VoidCallback onPrev;
  final bool canProceed;

  const _Step6Profile({required this.profileType, required this.onProfileTypeChanged, required this.onNext, required this.onPrev, this.canProceed = false});

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Perfil',
      subtitle: 'Escolha como você quer usar a plataforma',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          _ProfileOptionWithInfo(
            selected: profileType == 'curioso',
            icon: Icons.explore_outlined,
            title: 'Curioso',
            subtitle: 'Conhecer e explorar a plataforma',
            infoBody: _profileInfoCurioso,
            onTap: () => onProfileTypeChanged('curioso'),
            onInfo: () => _showProfileInfoDialog(context, 'Curioso', _profileInfoCurioso),
          ),
          const SizedBox(height: 12),
          _ProfileOptionWithInfo(
            selected: profileType == 'inteligente',
            icon: Icons.lightbulb_outline,
            title: 'Inteligente',
            subtitle: 'Uma semana de cota',
            infoBody: _profileInfoInteligente,
            onTap: () => onProfileTypeChanged('inteligente'),
            onInfo: () => _showProfileInfoDialog(context, 'Inteligente', _profileInfoInteligente),
          ),
          const SizedBox(height: 12),
          _ProfileOptionWithInfo(
            selected: profileType == 'sabio',
            icon: Icons.auto_awesome,
            title: 'Sábio',
            subtitle: 'Duas ou mais semanas de cota',
            infoBody: _profileInfoSabio,
            onTap: () => onProfileTypeChanged('sabio'),
            onInfo: () => _showProfileInfoDialog(context, 'Sábio', _profileInfoSabio),
          ),
          const SizedBox(height: 28),
          _RowButtons(onPrev: onPrev, onNext: onNext, canProceed: canProceed),
        ],
      ),
    );
  }
}

class _ProfileOptionWithInfo extends StatelessWidget {
  final bool selected;
  final IconData icon;
  final String title;
  final String subtitle;
  final String infoBody;
  final VoidCallback onTap;
  final VoidCallback onInfo;

  const _ProfileOptionWithInfo({
    required this.selected,
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.infoBody,
    required this.onTap,
    required this.onInfo,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: selected ? _primaryLight.withValues(alpha: 0.7) : _inputBg,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: selected ? _primary : _border, width: selected ? 2 : 1),
            ),
            child: Row(
              children: [
                Icon(icon, size: 28, color: selected ? _primary : _textSecondary),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: selected ? _primary : _textPrimary)),
                      const SizedBox(height: 2),
                      Text(subtitle, style: TextStyle(fontSize: 13, color: _textSecondary)),
                    ],
                  ),
                ),
                TextButton(
                  onPressed: () {
                    onInfo();
                  },
                  style: TextButton.styleFrom(
                    minimumSize: Size.zero,
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  child: Text('Informações do perfil', style: TextStyle(fontSize: 12, color: _primary, fontWeight: FontWeight.w500)),
                ),
                if (selected) Icon(Icons.check_circle_rounded, color: _primary, size: 24),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

// --- Step 7: Termos e envio ---
class _Step7Terms extends StatelessWidget {
  final bool acceptTerms;
  final bool acceptPromotional;
  final ValueChanged<bool> onAcceptTermsChanged;
  final ValueChanged<bool> onAcceptPromotionalChanged;
  final VoidCallback onPrev;
  final VoidCallback onSubmit;
  final bool loading;

  const _Step7Terms({
    required this.acceptTerms,
    required this.acceptPromotional,
    required this.onAcceptTermsChanged,
    required this.onAcceptPromotionalChanged,
    required this.onPrev,
    required this.onSubmit,
    required this.loading,
  });

  @override
  Widget build(BuildContext context) {
    return _StepCard(
      title: 'Termos e conclusão',
      subtitle: 'Leia e aceite para finalizar seu cadastro',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          CheckboxListTile(
            value: acceptTerms,
            onChanged: (v) => onAcceptTermsChanged(v ?? false),
            title: const Text('Li e aceito os Termos e Condições de Uso e Políticas de Privacidade *', style: TextStyle(fontSize: 14)),
            activeColor: _primary,
            controlAffinity: ListTileControlAffinity.leading,
            contentPadding: EdgeInsets.zero,
          ),
          const SizedBox(height: 8),
          CheckboxListTile(
            value: acceptPromotional,
            onChanged: (v) => onAcceptPromotionalChanged(v ?? false),
            title: const Text('Aceito participar de descontos, ofertas e promoções da plataforma', style: TextStyle(fontSize: 14)),
            activeColor: _primary,
            controlAffinity: ListTileControlAffinity.leading,
            contentPadding: EdgeInsets.zero,
          ),
          const SizedBox(height: 28),
          Row(
            children: [
              Expanded(child: OutlinedButton(onPressed: onPrev, style: OutlinedButton.styleFrom(foregroundColor: _primary, side: const BorderSide(color: _primary), padding: const EdgeInsets.symmetric(vertical: 16)), child: const Text('Voltar'))),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: _PrimaryButton(
                  text: loading ? 'Enviando...' : 'Concluir cadastro',
                  onPressed: (loading || !acceptTerms || !acceptPromotional) ? null : onSubmit,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

// --- Componentes reutilizáveis ---
class _StepCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final Widget child;

  const _StepCard({required this.title, required this.subtitle, required this.child});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(24, 8, 24, 32),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 28),
        decoration: BoxDecoration(
          color: _cardBg,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(color: Colors.black.withValues(alpha: 0.06), blurRadius: 24, offset: const Offset(0, 8)),
            BoxShadow(color: Colors.black.withValues(alpha: 0.03), blurRadius: 8, offset: const Offset(0, 2)),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w600, color: _textPrimary)),
            const SizedBox(height: 6),
            Text(subtitle, style: TextStyle(fontSize: 14, color: _textSecondary, height: 1.35)),
            const SizedBox(height: 24),
            child,
          ],
        ),
      ),
    );
  }
}

class _Label extends StatelessWidget {
  final String text;

  const _Label(this.text);

  @override
  Widget build(BuildContext context) {
    return Text(text, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: _textSecondary));
  }
}

class _Input extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  final bool obscure;
  final TextInputType? keyboard;
  final String? Function(String?)? validator;
  final List<TextInputFormatter>? inputFormatters;

  const _Input({
    required this.controller,
    required this.hint,
    this.obscure = false,
    this.keyboard,
    this.validator,
    this.inputFormatters,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      obscureText: obscure,
      keyboardType: keyboard,
      validator: validator,
      inputFormatters: inputFormatters,
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: TextStyle(color: Colors.grey[400], fontSize: 15),
        filled: true,
        fillColor: _inputBg,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _border)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: _primary, width: 2)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
      ),
    );
  }
}

class _PrimaryButton extends StatelessWidget {
  final String text;
  final VoidCallback? onPressed;

  const _PrimaryButton({required this.text, this.onPressed});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 54,
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(backgroundColor: _primary, foregroundColor: Colors.white, elevation: 0, shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14))),
        child: Text(text, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w600)),
      ),
    );
  }
}

class _RowButtons extends StatelessWidget {
  final VoidCallback onPrev;
  final VoidCallback onNext;
  final GlobalKey<FormState>? formKey;
  final bool canProceed;

  const _RowButtons({required this.onPrev, required this.onNext, this.formKey, this.canProceed = true});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(child: OutlinedButton(onPressed: onPrev, style: OutlinedButton.styleFrom(foregroundColor: _primary, side: const BorderSide(color: _primary), padding: const EdgeInsets.symmetric(vertical: 16)), child: const Text('Voltar'))),
        const SizedBox(width: 12),
        Expanded(
          child: _PrimaryButton(
            text: 'Continuar',
            onPressed: canProceed
                ? () {
                    if (formKey != null && (formKey!.currentState?.validate() ?? false) == false) return;
                    onNext();
                  }
                : null,
          ),
        ),
      ],
    );
  }
}

class _OptionCard extends StatelessWidget {
  final bool selected;
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _OptionCard({required this.selected, required this.icon, required this.title, required this.subtitle, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: selected ? _primaryLight.withValues(alpha: 0.7) : _inputBg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: selected ? _primary : _border, width: selected ? 2 : 1),
        ),
        child: Row(
          children: [
            Icon(icon, size: 28, color: selected ? _primary : _textSecondary),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600, color: selected ? _primary : _textPrimary)),
                  const SizedBox(height: 2),
                  Text(subtitle, style: TextStyle(fontSize: 13, color: _textSecondary)),
                ],
              ),
            ),
            if (selected) Icon(Icons.check_circle_rounded, color: _primary, size: 24),
          ],
        ),
      ),
    );
  }
}

class _PhotoBox extends StatelessWidget {
  final File? file;
  final String label;
  final VoidCallback onTap;

  const _PhotoBox({this.file, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTap: onTap,
      child: Container(
        height: 120,
        decoration: BoxDecoration(
          color: _inputBg,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: _border),
        ),
        child: file != null
            ? ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.file(file!, fit: BoxFit.cover, width: double.infinity),
              )
            : Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.add_photo_alternate_outlined, size: 40, color: _textSecondary),
                    const SizedBox(height: 8),
                    Text(label, style: TextStyle(fontSize: 13, color: _textSecondary)),
                  ],
                ),
              ),
      ),
    );
  }
}
