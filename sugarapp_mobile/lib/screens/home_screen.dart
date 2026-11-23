import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/daily_log_model.dart';
import '../models/patient_model.dart';
import '../services/api_service.dart';
import 'add_log_screen.dart';
import 'login_screen.dart';

class HomeScreen extends StatefulWidget {
  final PatientModel patient;
  final List<PatientModel> patients;
  final Function(PatientModel) onPatientChanged;
  final Function(int) onNavigateToTab;

  const HomeScreen({
    super.key,
    required this.patient,
    required this.patients,
    required this.onPatientChanged,
    required this.onNavigateToTab,
  });

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  DailyLogModel? _todayLog;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadTodayLog();
  }

  @override
  void didUpdateWidget(HomeScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.patient.id != widget.patient.id) {
      _loadTodayLog();
    }
  }

  Future<void> _loadTodayLog() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final log = await ApiService.getTodayLog(widget.patient.id);
      if (mounted) {
        setState(() {
          _todayLog = log;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString().replaceAll('Exception:', '').trim();
          _isLoading = false;
        });
      }
    }
  }

  void _openAddOrEditLog([DailyLogModel? logToEdit]) async {
    final res = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => AddLogScreen(
          patient: widget.patient,
          existingLog: logToEdit ?? _todayLog,
        ),
      ),
    );
    if (res == true) {
      _loadTodayLog();
    }
  }

  Color _getSugarStatusColor(int? sugar, int low, int high) {
    if (sugar == null) return Colors.grey;
    if (sugar < low) return const Color(0xFFF59E0B); // Low
    if (sugar > high) return const Color(0xFFEF4444); // High
    return const Color(0xFF10B981); // Normal
  }

  String _getSugarStatusText(int? sugar, int low, int high) {
    if (sugar == null) return 'No Reading';
    if (sugar < low) return 'Low Sugar';
    if (sugar > high) return 'High Sugar';
    return 'In Target';
  }

  @override
  Widget build(BuildContext context) {
    final patient = widget.patient;
    final todayStr = DateFormat('EEEE, MMM d, yyyy').format(DateTime.now());

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0.5,
        title: Row(
          children: [
            const Icon(Icons.bloodtype, color: Color(0xFF0F766E), size: 28),
            const SizedBox(width: 8),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'SugarApp',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                ),
                Text(
                  todayStr,
                  style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout_outlined, color: Colors.redAccent),
            tooltip: 'Logout',
            onPressed: () async {
              await ApiService.clearAuth();
              if (mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const LoginScreen()),
                  (route) => false,
                );
              }
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF0F766E)))
          : _errorMessage != null
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(_errorMessage!, style: const TextStyle(color: Colors.red)),
                      const SizedBox(height: 12),
                      ElevatedButton(onPressed: _loadTodayLog, child: const Text('Retry')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadTodayLog,
                  color: const Color(0xFF0F766E),
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Active Patient Card & Switcher
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(14),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Row(
                            children: [
                              CircleAvatar(
                                backgroundColor: const Color(0xFF0F766E).withValues(alpha: 0.12),
                                child: Text(
                                  patient.name.isNotEmpty ? patient.name[0].toUpperCase() : 'P',
                                  style: const TextStyle(color: Color(0xFF0F766E), fontWeight: FontWeight.bold),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      patient.name,
                                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Color(0xFF1E293B)),
                                    ),
                                    Text(
                                      'Target: ${patient.targetLow}–${patient.targetHigh} mg/dL | AM: ${patient.usualMorningDose.toStringAsFixed(0)}u PM: ${patient.usualNightDose.toStringAsFixed(0)}u',
                                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                    ),
                                  ],
                                ),
                              ),
                              if (widget.patients.length > 1)
                                PopupMenuButton<PatientModel>(
                                  icon: const Icon(Icons.swap_horiz, color: Color(0xFF0F766E)),
                                  tooltip: 'Switch Patient',
                                  onSelected: widget.onPatientChanged,
                                  itemBuilder: (context) {
                                    return widget.patients.map((p) {
                                      return PopupMenuItem(value: p, child: Text(p.name));
                                    }).toList();
                                  },
                                ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Doctor Check Alert if flagged
                        if (_todayLog?.needsCheck == true)
                          Container(
                            margin: const EdgeInsets.only(bottom: 16),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: Colors.red.shade50,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: Colors.red.shade300),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.warning_amber_rounded, color: Colors.red, size: 28),
                                const SizedBox(width: 12),
                                const Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Doctor Attention Required!',
                                        style: TextStyle(fontWeight: FontWeight.bold, color: Colors.red, fontSize: 14),
                                      ),
                                      SizedBox(height: 2),
                                      Text(
                                        'This log is flagged for medical review.',
                                        style: TextStyle(color: Color(0xFF991B1B), fontSize: 12),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),

                        // Hero Card (Latest Reading & Edit Option)
                        _buildHeroCard(patient, _todayLog),
                        const SizedBox(height: 20),

                        // Navigation Quick Buttons: History & Reports
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF0F766E),
                                  side: const BorderSide(color: Color(0xFF0F766E)),
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                icon: const Icon(Icons.history, size: 18),
                                label: const Text('View History', style: TextStyle(fontWeight: FontWeight.bold)),
                                onPressed: () => widget.onNavigateToTab(1),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: OutlinedButton.icon(
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF0F766E),
                                  side: const BorderSide(color: Color(0xFF0F766E)),
                                  padding: const EdgeInsets.symmetric(vertical: 12),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                icon: const Icon(Icons.bar_chart, size: 18),
                                label: const Text('View Reports', style: TextStyle(fontWeight: FontWeight.bold)),
                                onPressed: () => widget.onNavigateToTab(2),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),

                        // Section Heading
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              "Today's Readings",
                              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)),
                            ),
                            TextButton.icon(
                              icon: const Icon(Icons.edit, size: 16, color: Color(0xFF0F766E)),
                              label: Text(
                                _todayLog != null ? 'Edit Readings' : '+ Add Readings',
                                style: const TextStyle(color: Color(0xFF0F766E), fontWeight: FontWeight.bold),
                              ),
                              onPressed: () => _openAddOrEditLog(_todayLog),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Meal 1: Nashta
                        _buildMealCard(
                          title: 'Nashta (Breakfast)',
                          icon: Icons.wb_sunny_outlined,
                          color: Colors.orange,
                          beforeSugar: _todayLog?.nashtaSugarBefore,
                          afterSugar: _todayLog?.nashtaSugarAfter,
                          insulin: _todayLog?.insulinMorning,
                          targetLow: patient.targetLow,
                          targetHigh: patient.targetHigh,
                          onEdit: () => _openAddOrEditLog(_todayLog),
                        ),
                        const SizedBox(height: 12),

                        // Meal 2: Lunch
                        _buildMealCard(
                          title: 'Lunch',
                          icon: Icons.wb_cloudy_outlined,
                          color: Colors.blue,
                          beforeSugar: _todayLog?.lunchSugarBefore,
                          afterSugar: _todayLog?.lunchSugarAfter,
                          insulin: _todayLog?.insulinLunch,
                          targetLow: patient.targetLow,
                          targetHigh: patient.targetHigh,
                          onEdit: () => _openAddOrEditLog(_todayLog),
                        ),
                        const SizedBox(height: 12),

                        // Meal 3: Dinner
                        _buildMealCard(
                          title: 'Dinner',
                          icon: Icons.nights_stay_outlined,
                          color: Colors.indigo,
                          beforeSugar: _todayLog?.dinnerSugarBefore,
                          afterSugar: _todayLog?.dinnerSugarAfter,
                          insulin: _todayLog?.insulinNight,
                          targetLow: patient.targetLow,
                          targetHigh: patient.targetHigh,
                          onEdit: () => _openAddOrEditLog(_todayLog),
                        ),
                        const SizedBox(height: 80),
                      ],
                    ),
                  ),
                ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF0F766E),
        foregroundColor: Colors.white,
        icon: Icon(_todayLog != null ? Icons.edit : Icons.add),
        label: Text(
          _todayLog != null ? 'Edit Today\'s Reading' : 'Record Reading',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        onPressed: () => _openAddOrEditLog(_todayLog),
      ),
    );
  }

  Widget _buildHeroCard(PatientModel patient, DailyLogModel? log) {
    final low = patient.targetLow;
    final high = patient.targetHigh;
    final latestVal = log?.latestReading;
    final color = _getSugarStatusColor(latestVal, low, high);
    final statusText = _getSugarStatusText(latestVal, low, high);

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F766E), Color(0xFF115E59)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F766E).withValues(alpha: 0.25),
            blurRadius: 15,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'LATEST READING',
                style: TextStyle(
                  color: Colors.white70,
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  letterSpacing: 1,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(20)),
                child: Text(
                  statusText,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              Text(
                latestVal != null ? '$latestVal' : '--',
                style: const TextStyle(color: Colors.white, fontSize: 48, fontWeight: FontWeight.w900),
              ),
              const SizedBox(width: 8),
              const Text('mg/dL', style: TextStyle(color: Colors.white70, fontSize: 18)),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            log?.latestReadingMeal ?? 'No readings logged today yet',
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
          const Divider(color: Colors.white24, height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Target: $low - $high mg/dL', style: const TextStyle(color: Colors.white70, fontSize: 13)),
              Text(
                'Total Insulin: ${(log?.totalInsulin ?? 0.0).toStringAsFixed(1)} units',
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
              ),
            ],
          ),
          if (log != null) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.white,
                  foregroundColor: const Color(0xFF0F766E),
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  elevation: 0,
                ),
                icon: const Icon(Icons.edit, size: 18),
                label: const Text('Edit Today\'s Reading', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                onPressed: () => _openAddOrEditLog(log),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildMealCard({
    required String title,
    required IconData icon,
    required MaterialColor color,
    required int? beforeSugar,
    required int? afterSugar,
    required double? insulin,
    required int targetLow,
    required int targetHigh,
    required VoidCallback onEdit,
  }) {
    return InkWell(
      onTap: onEdit,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
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
                const Spacer(),
                if (insulin != null && insulin > 0)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    margin: const EdgeInsets.only(right: 6),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      'Insulin: ${insulin.toStringAsFixed(0)}u',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
                    ),
                  ),
                IconButton(
                  icon: const Icon(Icons.edit_outlined, size: 18, color: Color(0xFF0F766E)),
                  tooltip: 'Edit reading',
                  constraints: const BoxConstraints(),
                  padding: EdgeInsets.zero,
                  onPressed: onEdit,
                ),
              ],
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: _buildReadingChip(
                    label: 'Before Meal',
                    value: beforeSugar,
                    targetLow: targetLow,
                    targetHigh: targetHigh,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _buildReadingChip(
                    label: 'After Meal',
                    value: afterSugar,
                    targetLow: targetLow,
                    targetHigh: targetHigh,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildReadingChip({
    required String label,
    required int? value,
    required int targetLow,
    required int targetHigh,
  }) {
    final statusColor = _getSugarStatusColor(value, targetLow, targetHigh);

    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: value != null ? statusColor.withValues(alpha: 0.3) : const Color(0xFFE2E8F0),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF64748B))),
          const SizedBox(height: 4),
          Row(
            children: [
              Text(
                value != null ? '$value' : '--',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: value != null ? statusColor : const Color(0xFF94A3B8),
                ),
              ),
              if (value != null) ...[
                const SizedBox(width: 4),
                const Text('mg/dL', style: TextStyle(fontSize: 10, color: Color(0xFF64748B))),
              ],
            ],
          ),
        ],
      ),
    );
  }
}
