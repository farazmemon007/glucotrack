class PatientModel {
  final int id;
  final int? caregiverId;
  final int? userId;
  final String name;
  final int? age;
  final String? gender;
  final String? phone;
  final String? diabetesType;
  final int targetLow;
  final int targetHigh;
  final double usualMorningDose;
  final double usualNightDose;
  final String? notes;

  PatientModel({
    required this.id,
    this.caregiverId,
    this.userId,
    required this.name,
    this.age,
    this.gender,
    this.phone,
    this.diabetesType,
    this.targetLow = 80,
    this.targetHigh = 180,
    this.usualMorningDose = 30.0,
    this.usualNightDose = 30.0,
    this.notes,
  });

  factory PatientModel.fromJson(Map<String, dynamic> json) {
    return PatientModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      caregiverId: json['caregiver_id'] != null ? int.tryParse(json['caregiver_id'].toString()) : null,
      userId: json['user_id'] != null ? int.tryParse(json['user_id'].toString()) : null,
      name: json['name'] ?? 'Unknown',
      age: json['age'] != null ? int.tryParse(json['age'].toString()) : null,
      gender: json['gender'] ?? 'male',
      phone: json['phone'],
      diabetesType: json['diabetes_type'] ?? 'type2',
      targetLow: json['target_low'] != null ? int.tryParse(json['target_low'].toString()) ?? 80 : 80,
      targetHigh: json['target_high'] != null ? int.tryParse(json['target_high'].toString()) ?? 180 : 180,
      usualMorningDose: json['usual_morning_dose'] != null ? double.tryParse(json['usual_morning_dose'].toString()) ?? 30.0 : 30.0,
      usualNightDose: json['usual_night_dose'] != null ? double.tryParse(json['usual_night_dose'].toString()) ?? 30.0 : 30.0,
      notes: json['notes'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'age': age,
      'gender': gender,
      'phone': phone,
      'diabetes_type': diabetesType,
      'target_low': targetLow,
      'target_high': targetHigh,
      'usual_morning_dose': usualMorningDose,
      'usual_night_dose': usualNightDose,
      'notes': notes,
    };
  }
}
