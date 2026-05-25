class UserModel {
  final int id;
  final String name;
  final String email;
  final String? whatsapp;
  final UserProfile? profile;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.whatsapp,
    this.profile,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      whatsapp: json['whatsapp'] as String?,
      profile: json['profile'] != null ? UserProfile.fromJson(json['profile'] as Map<String, dynamic>) : null,
    );
  }
}

class UserProfile {
  final int id;
  final String? fullName;
  final String? profileType;
  final bool kycCompleted;

  UserProfile({
    required this.id,
    this.fullName,
    this.profileType,
    this.kycCompleted = false,
  });

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      id: json['id'] as int,
      fullName: json['full_name'] as String?,
      profileType: json['profile_type'] as String?,
      kycCompleted: json['kyc_completed'] as bool? ?? false,
    );
  }
}
