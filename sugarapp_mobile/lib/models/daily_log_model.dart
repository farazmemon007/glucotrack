class DailyLogModel {
  final int id;
  final int patientId;
  final String logDate;
  final String? logTime;

  // Nashta (Breakfast)
  final int? nashtaSugarBefore;
  final int? nashtaSugarAfter;
  final String? nashtaTime;
  final double? insulinMorning;

  // Lunch
  final int? lunchSugarBefore;
  final int? lunchSugarAfter;
  final String? lunchTime;
  final double? insulinLunch;

  // Dinner
  final int? dinnerSugarBefore;
  final int? dinnerSugarAfter;
  final String? dinnerTime;
  final double? insulinNight;

  final String? notes;
  final bool needsCheck;

  DailyLogModel({
    required this.id,
    required this.patientId,
    required this.logDate,
    this.logTime,
    this.nashtaSugarBefore,
    this.nashtaSugarAfter,
    this.nashtaTime,
    this.insulinMorning,
    this.lunchSugarBefore,
    this.lunchSugarAfter,
    this.lunchTime,
    this.insulinLunch,
    this.dinnerSugarBefore,
    this.dinnerSugarAfter,
    this.dinnerTime,
    this.insulinNight,
    this.notes,
    this.needsCheck = false,
  });

  factory DailyLogModel.fromJson(Map<String, dynamic> json) {
    int? parseInt(dynamic v) => v != null ? int.tryParse(v.toString()) : null;
    double? parseDouble(dynamic v) => v != null ? double.tryParse(v.toString()) : null;

    return DailyLogModel(
      id: json['id'] is int ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      patientId: json['patient_id'] is int ? json['patient_id'] : int.tryParse(json['patient_id'].toString()) ?? 0,
      logDate: json['log_date']?.toString().split('T').first ?? '',
      logTime: json['log_time']?.toString(),
      nashtaSugarBefore: parseInt(json['nashta_sugar_before'] ?? json['nashta_sugar']),
      nashtaSugarAfter: parseInt(json['nashta_sugar_after']),
      nashtaTime: json['nashta_time_before'] ?? json['nashta_time_after'] ?? json['nashta_time'],
      insulinMorning: parseDouble(json['insulin_morning']),
      lunchSugarBefore: parseInt(json['lunch_sugar_before'] ?? json['lunch_sugar']),
      lunchSugarAfter: parseInt(json['lunch_sugar_after']),
      lunchTime: json['lunch_time_before'] ?? json['lunch_time_after'] ?? json['lunch_time'],
      insulinLunch: parseDouble(json['insulin_lunch']),
      dinnerSugarBefore: parseInt(json['dinner_sugar_before'] ?? json['dinner_sugar']),
      dinnerSugarAfter: parseInt(json['dinner_sugar_after']),
      dinnerTime: json['dinner_time_before'] ?? json['dinner_time_after'] ?? json['dinner_time'],
      insulinNight: parseDouble(json['insulin_night']),
      notes: json['notes'],
      needsCheck: json['needs_check'] == true || json['needs_check'] == 1 || json['needs_check'] == '1',
    );
  }

  // Get latest valid reading for hero card
  int? get latestReading {
    return dinnerSugarAfter ??
        dinnerSugarBefore ??
        lunchSugarAfter ??
        lunchSugarBefore ??
        nashtaSugarAfter ??
        nashtaSugarBefore;
  }

  String get latestReadingMeal {
    if (dinnerSugarAfter != null) return 'Dinner (After)';
    if (dinnerSugarBefore != null) return 'Dinner (Before)';
    if (lunchSugarAfter != null) return 'Lunch (After)';
    if (lunchSugarBefore != null) return 'Lunch (Before)';
    if (nashtaSugarAfter != null) return 'Nashta (After)';
    if (nashtaSugarBefore != null) return 'Nashta (Fasting/Before)';
    return 'No readings today';
  }

  double get totalInsulin {
    double total = 0.0;
    if (insulinMorning != null) total += insulinMorning!;
    if (insulinLunch != null) total += insulinLunch!;
    if (insulinNight != null) total += insulinNight!;
    return total;
  }
}
