class VerifyOtpModel {
  final String email;
  final String code;

  VerifyOtpModel({required this.email, required this.code});

  Map<String, dynamic> toJson() => {
    "email": email,
    "code": code,
  };
}
