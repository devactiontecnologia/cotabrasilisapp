<?php

namespace App\Http\Controllers;

use App\Services\PlatformDataExchangeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminDataExchangeController extends Controller
{
    public function index(PlatformDataExchangeService $service)
    {
        $tables = $service->getExportableTables();

        return view('admin.data-exchange.index', compact('tables'));
    }

    public function export(PlatformDataExchangeService $service)
    {
        try {
            $path = $service->buildExportZip();
            $name = 'cotabrasilis-dados-' . now()->format('Y-m-d-His') . '.zip';

            return response()->download($path, $name)->deleteFileAfterSend(true);
        } catch (Throwable $e) {
            Log::error('Exportação de dados: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()
                ->route('admin.data-exchange.index')
                ->with('error', 'Não foi possível gerar o arquivo. Verifique os logs e se a extensão ZIP do PHP está ativa.');
        }
    }

    public function import(Request $request, PlatformDataExchangeService $service)
    {
        $request->validate([
            'archive' => 'required|file|mimes:zip|max:512000',
            'confirm_replace' => 'required|accepted',
        ], [
            'archive.required' => 'Envie um arquivo ZIP exportado pela plataforma.',
            'confirm_replace.accepted' => 'Confirme que entende que os dados atuais serão substituídos.',
        ]);

        $file = $request->file('archive');
        if (!$file || !$file->isValid()) {
            return redirect()
                ->route('admin.data-exchange.index')
                ->with('error', 'Arquivo inválido.');
        }

        $tmp = $file->getRealPath();
        if (!$tmp || !is_readable($tmp)) {
            return redirect()
                ->route('admin.data-exchange.index')
                ->with('error', 'Não foi possível ler o arquivo enviado.');
        }

        try {
            $result = $service->importFromZip($tmp);

            return redirect()
                ->route('admin.data-exchange.index')
                ->with('success', 'Importação concluída. ' . $result['count'] . ' tabela(s) preenchida(s) a partir do ZIP. As demais tabelas exportáveis foram esvaziadas.');
        } catch (Throwable $e) {
            Log::error('Importação de dados: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()
                ->route('admin.data-exchange.index')
                ->with('error', 'Falha na importação: ' . $e->getMessage());
        }
    }
}
