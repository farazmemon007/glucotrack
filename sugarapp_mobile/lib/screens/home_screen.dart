import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/daily_log_model.dart';
import '../models/patient_model.dart';
import '../services/api_service.dart';
import 'add_log_screen.dart';
import 'login_screen.dart';
import 'logs_history_screen.dart';
import 'patients_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<PatientModel> _patients = [];
  PatientModel? _activePatient;
  DailyLogModel? _todayLog;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final patients = await ApiService.getPatients();
      if (patients.isEmpty) {
        throw Exception('No patients found.');
      }

      final savedPatientId = await ApiService.getActivePatientId();
      PatientModel active = patients.first;
      if (savedPatientId != null) {
        active = patients.firstWhere(
          (p) => p.id == savedPatientId,
          orElse: () => patients.first,
        );
      }

      final todayLog = await ApiService.getTodayLog(active.id);

      if (mounted) {
        setState(() {
          _patients = patients;
          _activePatient = active;
          _todayLog = todayLog;
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

  void _switchPatient(PatientModel newPatient) async {
    await ApiService.setActivePatientId(newPatient.id);
    setState(() {
      _activePatient = newPatient;
      _isLoading = true;
    });

    try {
      final log = await ApiService.getTodayLog(newPatient.id);
      if (mounted) {
        setState(() {
          _todayLog = log;
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  Color _getSugarStatusColor(int? sugar, int low, int high) {
    if (sugar == null) return Colors.grey;
    if (sugar < low) return const Color(0xFFF59E0B); // Amber / Low
    if (sugar > high) return const Color(0xFFEF4444); // Red / High
    return const Color(0xFF10B981); // Emerald / Normal
  }

  String _getSugarStatusText(int? sugar, int low, int high) {
    if (sugar == null) return 'No Reading';
    if (sugar < low) return 'Low Sugar';
    if (sugar > high) return 'High Sugar';
    return 'In Target';
  }

  @override
  Widget build(BuildContext context) {
    final patient = _activePatient;
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
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
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
            icon: const Icon(Icons.people_outline, color: Color(0xFF0F766E)),
            tooltip: 'Patients List',
            onPressed: () async {
              await Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const PatientsScreen()),
              );
              _loadData();
            },
          ),
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
                      ElevatedButton(
                        onPressed: _loadData,
                        child: const Text('Retry'),
                      ),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _loadData,
                  color: const Color(0xFF0F766E),
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Patient Selector Card
                        if (_patients.isNotEmpty)
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
                                  backgroundColor: const Color(0xFF0F766E).withOpacity(0.12),
                                  child: Text(
                                    patient?.name.isNotEmpty == true ? patient!.name[0].toUpperCase() : 'P',
                                    style: const TextStyle(
                                      color: Color(0xFF0F766E),
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        patient?.name ?? '',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          fontSize: 16,
                                          color: Color(0xFF1E293B),
                                        ),
                                      ),
                                      Text(
                                        'Type: ${(patient?.diabetesType ?? "type2").toUpperCase()} | Target: ${patient?.targetLow}-${patient?.targetHigh} mg/dL',
                                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                      ),
                                    ],
                                  ),
                                ),
                                if (_patients.length > 1)
                                  PopupMenuButton<PatientModel>(
                                    icon: const Icon(Icons.swap_horiz, color: Color(0xFF0F766E)),
                                    tooltip: 'Switch Patient',
                                    onSelected: _switchPatient,
                                    itemBuilder: (context) {
                                      return _patients.map((p) {
                                        return PopupMenuItem(
                                          value: p,
                                          child: Text(p.name),
                                        );
                                      }).toList();
                                    },
                                  ),
                              ],
                            ),
                          ),
                        const SizedBox(height: 16),

                        // Needs Check Alert Banner (if flagged)
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
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: const [
                                      Text(
                                        'Doctor Attention Required!',
                                        style: TextStyle(
                                          fontWeight: FontWeight.bold,
                                          color: Colors.red,
                                          fontSize: 14,
                                        ),
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

                        // Hero Card: Latest Sugar Reading
                        _buildHeroCard(patient, _todayLog),
                        const SizedBox(height: 20),

                        // Meal Readings Breakdown
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              "Today's Readings",
                              style: TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF1E293B),
                              ),
                            ),
                            TextButton.icon(
                              icon: const Icon(Icons.history, size: 18, color: Color(0xFF0F766E)),
                              label: const Text('Full History', style: TextStyle(color: Color(0xFF0F766E))),
                              onPressed: () {
                                if (patient != null) {
                                  Navigator.push(
                                    context,
                                    MaterialPageRoute(
                                      builder: (_) => LogsHistoryScreen(patient: patient),
                                    ),
                                  );
                                }
                              },
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Meal 1: Nashta (Breakfast)
                        _buildMealCard(
                          title: 'Nashta (Breakfast)',
                          icon: Icons.wb_sunny_outlined,
                          color: Colors.orange,
                          beforeSugar: _todayLog?.nashtaSugarBefore,
                          afterSugar: _todayLog?.nashtaSugarAfter,
                          insulin: _todayLog?.insulinMorning,
                          targetLow: patient?.targetLow ?? 80,
                          targetHigh: patient?.targetHigh ?? 180,
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
                          targetLow: patient?.targetLow ?? 80,
                          targetHigh: patient?.targetHigh ?? 180,
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
                          targetLow: patient?.targetLow ?? 80,
                          targetHigh: patient?.targetHigh ?? 180,
                        ),
                        const SizedBox(height: 80),
                      ],
                    ),
                  ),
                ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF0F766E),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add),
        label: const Text('Record Reading', style: TextStyle(fontWeight: FontWeight.w600)),
        onPressed: () async {
          if (patient != null) {
            final res = await Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => AddLogScreen(patient: patient, existingLog: _todayLog),
              ),
            );
            if (res == true) {
              _loadData();
            }
          }
        },
      ),
    );
  }

  Widget _buildHeroCard(PatientModel? patient, DailyLogModel? log) {
    final low = patient?.targetLow ?? 80;
    final high = patient?.targetHigh ?? 180;
    final latestVal = log?.latestReading;
    final color = _getSugarStatusColor(latestVal, low, high);
    final statusText = _getSugarStatusText(latestVal, low, high);

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            const Color(0xFF0F766E),
            const Color(0xFF115E59),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF0F766E).withOpacity(0.25),
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
                decoration: BoxDecoration(
                  color: color,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  statusText,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 12,
                  ),
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
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 48,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(width: 8),
              const Text(
                'mg/dL',
                style: TextStyle(color: Colors.white70, fontSize: 18),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            log?.latestReadingMeal ?? 'Tap "+ Record Reading" to log today\'s sugar',
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
          const Divider(color: Colors.white24, height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Target: $low - $high mg/dL',
                style: const TextStyle(color: Colors.white70, fontSize: 13),
              ),
              Text(
                'Total Insulin: ${(log?.totalInsulin ?? 0.0).toStringAsFixed(1)} units',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ],
          ),
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
  }) {
    return Container(
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
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 15,
                  color: Color(0xFF1E293B),
                ),
              ),
              const Spacer(),
              if (insulin != null && insulin > 0)
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'Insulin: ${insulin.toStringAsFixed(0)}u',
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF475569),
                    ),
                  ),
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
          color: value != null ? statusColor.withOpacity(0.3) : const Color(0xFFE2E8F0),
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
                const Text(
                  'mg/dL',
                  style: TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}
