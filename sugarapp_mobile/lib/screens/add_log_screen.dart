import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/daily_log_model.dart';
import '../models/patient_model.dart';
import '../services/api_service.dart';

class AddLogScreen extends StatefulWidget {
  final PatientModel patient;
  final DailyLogModel? existingLog;

  const AddLogScreen({
    super.key,
    required this.patient,
    this.existingLog,
  });

  @override
  State<AddLogScreen> createState() => _AddLogScreenState();
}

class _AddLogScreenState extends State<AddLogScreen> {
  final _formKey = GlobalKey<FormState>();

  late DateTime _selectedDate;
  bool _isLoading = false;
  String? _errorMessage;

  // Nashta controllers
  final _nashtaBeforeCtrl = TextEditingController();
  final _nashtaAfterCtrl = TextEditingController();
  final _insulinMorningCtrl = TextEditingController();

  // Lunch controllers
  final _lunchBeforeCtrl = TextEditingController();
  final _lunchAfterCtrl = TextEditingController();
  final _insulinLunchCtrl = TextEditingController();

  // Dinner controllers
  final _dinnerBeforeCtrl = TextEditingController();
  final _dinnerAfterCtrl = TextEditingController();
  final _insulinNightCtrl = TextEditingController();

  // Notes & Flag
  final _notesCtrl = TextEditingController();
  bool _needsCheck = false;

  @override
  void initState() {
    super.initState();
    _selectedDate = DateTime.now();

    final log = widget.existingLog;
    if (log != null) {
      if (log.nashtaSugarBefore != null) _nashtaBeforeCtrl.text = log.nashtaSugarBefore.toString();
      if (log.nashtaSugarAfter != null) _nashtaAfterCtrl.text = log.nashtaSugarAfter.toString();
      if (log.insulinMorning != null) _insulinMorningCtrl.text = log.insulinMorning.toString();

      if (log.lunchSugarBefore != null) _lunchBeforeCtrl.text = log.lunchSugarBefore.toString();
      if (log.lunchSugarAfter != null) _lunchAfterCtrl.text = log.lunchSugarAfter.toString();
      if (log.insulinLunch != null) _insulinLunchCtrl.text = log.insulinLunch.toString();

      if (log.dinnerSugarBefore != null) _dinnerBeforeCtrl.text = log.dinnerSugarBefore.toString();
      if (log.dinnerSugarAfter != null) _dinnerAfterCtrl.text = log.dinnerSugarAfter.toString();
      if (log.insulinNight != null) _insulinNightCtrl.text = log.insulinNight.toString();

      if (log.notes != null) _notesCtrl.text = log.notes!;
      _needsCheck = log.needsCheck;
    } else {
      // Default usual insulin doses from patient profile
      _insulinMorningCtrl.text = widget.patient.usualMorningDose.toStringAsFixed(0);
      _insulinNightCtrl.text = widget.patient.usualNightDose.toStringAsFixed(0);
    }
  }

  @override
  void dispose() {
    _nashtaBeforeCtrl.dispose();
    _nashtaAfterCtrl.dispose();
    _insulinMorningCtrl.dispose();
    _lunchBeforeCtrl.dispose();
    _lunchAfterCtrl.dispose();
    _insulinLunchCtrl.dispose();
    _dinnerBeforeCtrl.dispose();
    _dinnerAfterCtrl.dispose();
    _insulinNightCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
    );
    if (picked != null && picked != _selectedDate) {
      setState(() {
        _selectedDate = picked;
      });
      // Optionally fetch existing log for that picked date
      _fetchLogForDate(picked);
    }
  }

  Future<void> _fetchLogForDate(DateTime date) async {
    final dateStr = DateFormat('yyyy-MM-dd').format(date);
    try {
      final log = await ApiService.getTodayLog(widget.patient.id, dateStr);
      if (log != null && mounted) {
        setState(() {
          _nashtaBeforeCtrl.text = log.nashtaSugarBefore?.toString() ?? '';
          _nashtaAfterCtrl.text = log.nashtaSugarAfter?.toString() ?? '';
          _insulinMorningCtrl.text = log.insulinMorning?.toString() ?? '';

          _lunchBeforeCtrl.text = log.lunchSugarBefore?.toString() ?? '';
          _lunchAfterCtrl.text = log.lunchSugarAfter?.toString() ?? '';
          _insulinLunchCtrl.text = log.insulinLunch?.toString() ?? '';

          _dinnerBeforeCtrl.text = log.dinnerSugarBefore?.toString() ?? '';
          _dinnerAfterCtrl.text = log.dinnerSugarAfter?.toString() ?? '';
          _insulinNightCtrl.text = log.insulinNight?.toString() ?? '';

          _notesCtrl.text = log.notes ?? '';
          _needsCheck = log.needsCheck;
        });
      }
    } catch (_) {}
  }

  Future<void> _saveLog() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);

    final payload = <String, dynamic>{
      'log_date': dateStr,
      'needs_check': _needsCheck,
      if (_nashtaBeforeCtrl.text.isNotEmpty) 'nashta_sugar_before': int.tryParse(_nashtaBeforeCtrl.text),
      if (_nashtaAfterCtrl.text.isNotEmpty) 'nashta_sugar_after': int.tryParse(_nashtaAfterCtrl.text),
      if (_insulinMorningCtrl.text.isNotEmpty) 'insulin_morning': double.tryParse(_insulinMorningCtrl.text),

      if (_lunchBeforeCtrl.text.isNotEmpty) 'lunch_sugar_before': int.tryParse(_lunchBeforeCtrl.text),
      if (_lunchAfterCtrl.text.isNotEmpty) 'lunch_sugar_after': int.tryParse(_lunchAfterCtrl.text),
      if (_insulinLunchCtrl.text.isNotEmpty) 'insulin_lunch': double.tryParse(_insulinLunchCtrl.text),

      if (_dinnerBeforeCtrl.text.isNotEmpty) 'dinner_sugar_before': int.tryParse(_dinnerBeforeCtrl.text),
      if (_dinnerAfterCtrl.text.isNotEmpty) 'dinner_sugar_after': int.tryParse(_dinnerAfterCtrl.text),
      if (_insulinNightCtrl.text.isNotEmpty) 'insulin_night': double.tryParse(_insulinNightCtrl.text),

      if (_notesCtrl.text.isNotEmpty) 'notes': _notesCtrl.text.trim(),
    };

    try {
      await ApiService.saveLog(widget.patient.id, payload);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Sugar log saved successfully!'),
            backgroundColor: Color(0xFF10B981),
          ),
        );
        Navigator.pop(context, true);
      }
    } catch (e) {
      setState(() {
        _errorMessage = e.toString().replaceAll('Exception:', '').trim();
      });
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final formattedDate = DateFormat('EEEE, MMM d, yyyy').format(_selectedDate);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text('Record Sugar: ${widget.patient.name}'),
        backgroundColor: Colors.white,
        elevation: 0.5,
        foregroundColor: const Color(0xFF1E293B),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if (_errorMessage != null)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: Colors.red.shade50,
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red.shade200),
                  ),
                  child: Text(
                    _errorMessage!,
                    style: TextStyle(color: Colors.red.shade800, fontSize: 13),
                  ),
                ),

              // Date Selector Card
              InkWell(
                onTap: _pickDate,
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.calendar_month, color: Color(0xFF0F766E)),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Log Date', style: TextStyle(fontSize: 12, color: Color(0xFF64748B))),
                            Text(
                              formattedDate,
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_drop_down, color: Color(0xFF64748B)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Meal 1: Nashta
              _buildSectionCard(
                title: '1. Nashta (Breakfast)',
                icon: Icons.wb_sunny_outlined,
                color: Colors.orange,
                beforeCtrl: _nashtaBeforeCtrl,
                afterCtrl: _nashtaAfterCtrl,
                insulinCtrl: _insulinMorningCtrl,
                insulinLabel: 'Morning Insulin (units)',
              ),
              const SizedBox(height: 14),

              // Meal 2: Lunch
              _buildSectionCard(
                title: '2. Lunch',
                icon: Icons.wb_cloudy_outlined,
                color: Colors.blue,
                beforeCtrl: _lunchBeforeCtrl,
                afterCtrl: _lunchAfterCtrl,
                insulinCtrl: _insulinLunchCtrl,
                insulinLabel: 'Lunch Insulin (units)',
              ),
              const SizedBox(height: 14),

              // Meal 3: Dinner
              _buildSectionCard(
                title: '3. Dinner',
                icon: Icons.nights_stay_outlined,
                color: Colors.indigo,
                beforeCtrl: _dinnerBeforeCtrl,
                afterCtrl: _dinnerAfterCtrl,
                insulinCtrl: _insulinNightCtrl,
                insulinLabel: 'Night Insulin (units)',
              ),
              const SizedBox(height: 14),

              // Doctor Check Flag & Notes
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SwitchListTile(
                      contentPadding: EdgeInsets.zero,
                      title: const Text(
                        'Needs Doctor Check',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                      ),
                      subtitle: const Text(
                        'Flag this reading for physician review',
                        style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                      ),
                      activeColor: Colors.redAccent,
                      value: _needsCheck,
                      onChanged: (val) {
                        setState(() {
                          _needsCheck = val;
                        });
                      },
                    ),
                    const Divider(height: 20),
                    TextFormField(
                      controller: _notesCtrl,
                      maxLines: 2,
                      decoration: InputDecoration(
                        labelText: 'Notes / Symptoms / Remarks',
                        hintText: 'e.g. Felt dizzy, had extra sweet tea, etc.',
                        filled: true,
                        fillColor: const Color(0xFFF8FAFC),
                        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),

              // Save Button
              ElevatedButton(
                onPressed: _isLoading ? null : _saveLog,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF0F766E),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  elevation: 0,
                ),
                child: _isLoading
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text(
                        'Save Daily Log',
                        style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                      ),
              ),
              const SizedBox(height: 30),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildSectionCard({
    required String title,
    required IconData icon,
    required MaterialColor color,
    required TextEditingController beforeCtrl,
    required TextEditingController afterCtrl,
    required TextEditingController insulinCtrl,
    required String insulinLabel,
  }) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, color: color, size: 20),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: Color(0xFF1E293B)),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: TextFormField(
                  controller: beforeCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'Before Meal',
                    suffixText: 'mg/dL',
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: TextFormField(
                  controller: afterCtrl,
                  keyboardType: TextInputType.number,
                  decoration: InputDecoration(
                    labelText: 'After Meal',
                    suffixText: 'mg/dL',
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          TextFormField(
            controller: insulinCtrl,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            decoration: InputDecoration(
              labelText: insulinLabel,
              suffixText: 'units',
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
        ],
      ),
    );
  }
}
