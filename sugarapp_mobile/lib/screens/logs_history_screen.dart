import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/daily_log_model.dart';
import '../models/patient_model.dart';
import '../services/api_service.dart';
import 'add_log_screen.dart';

class LogsHistoryScreen extends StatefulWidget {
  final PatientModel patient;

  const LogsHistoryScreen({super.key, required this.patient});

  @override
  State<LogsHistoryScreen> createState() => _LogsHistoryScreenState();
}

class _LogsHistoryScreenState extends State<LogsHistoryScreen> {
  List<DailyLogModel> _logs = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchHistory();
  }

  Future<void> _fetchHistory() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final logs = await ApiService.getLogsHistory(widget.patient.id);
      if (mounted) {
        setState(() {
          _logs = logs;
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

  Color _getReadingColor(int? sugar) {
    if (sugar == null) return Colors.grey;
    if (sugar < widget.patient.targetLow) return const Color(0xFFF59E0B);
    if (sugar > widget.patient.targetHigh) return const Color(0xFFEF4444);
    return const Color(0xFF10B981);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text('${widget.patient.name} - History'),
        backgroundColor: Colors.white,
        elevation: 0.5,
        foregroundColor: const Color(0xFF1E293B),
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
                      ElevatedButton(onPressed: _fetchHistory, child: const Text('Retry')),
                    ],
                  ),
                )
              : _logs.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.event_note, size: 64, color: Colors.grey.shade400),
                          const SizedBox(height: 12),
                          const Text(
                            'No logs recorded yet',
                            style: TextStyle(fontSize: 16, color: Color(0xFF64748B)),
                          ),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _fetchHistory,
                      color: const Color(0xFF0F766E),
                      child: ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _logs.length,
                        itemBuilder: (context, index) {
                          final log = _logs[index];
                          DateTime? parsedDate;
                          try {
                            parsedDate = DateTime.parse(log.logDate);
                          } catch (_) {}

                          final displayDate = parsedDate != null
                              ? DateFormat('EEEE, MMM d, yyyy').format(parsedDate)
                              : log.logDate;

                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(
                                color: log.needsCheck ? Colors.red.shade300 : const Color(0xFFE2E8F0),
                              ),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withOpacity(0.02),
                                  blurRadius: 6,
                                  offset: const Offset(0, 2),
                                ),
                              ],
                            ),
                            child: InkWell(
                              borderRadius: BorderRadius.circular(14),
                              onTap: () async {
                                final res = await Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => AddLogScreen(
                                      patient: widget.patient,
                                      existingLog: log,
                                    ),
                                  ),
                                );
                                if (res == true) {
                                  _fetchHistory();
                                }
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Row(
                                          children: [
                                            const Icon(Icons.calendar_today, size: 16, color: Color(0xFF0F766E)),
                                            const SizedBox(width: 8),
                                            Text(
                                              displayDate,
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                            ),
                                          ],
                                        ),
                                        if (log.needsCheck)
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                            decoration: BoxDecoration(
                                              color: Colors.red.shade50,
                                              borderRadius: BorderRadius.circular(6),
                                            ),
                                            child: const Text(
                                              'Doctor Check',
                                              style: TextStyle(
                                                color: Colors.red,
                                                fontSize: 11,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ),
                                      ],
                                    ),
                                    const Divider(height: 18),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                                      children: [
                                        _buildMealSummary('Nashta', log.nashtaSugarBefore, log.nashtaSugarAfter),
                                        _buildMealSummary('Lunch', log.lunchSugarBefore, log.lunchSugarAfter),
                                        _buildMealSummary('Dinner', log.dinnerSugarBefore, log.dinnerSugarAfter),
                                      ],
                                    ),
                                    if (log.totalInsulin > 0 || (log.notes?.isNotEmpty ?? false)) ...[
                                      const SizedBox(height: 10),
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          if (log.totalInsulin > 0)
                                            Text(
                                              'Insulin: ${log.totalInsulin.toStringAsFixed(0)} units',
                                              style: const TextStyle(
                                                fontSize: 12,
                                                color: Color(0xFF0F766E),
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                          if (log.notes?.isNotEmpty ?? false)
                                            Expanded(
                                              child: Text(
                                                log.notes!,
                                                textAlign: TextAlign.end,
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                                              ),
                                            ),
                                        ],
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),
    );
  }

  Widget _buildMealSummary(String meal, int? before, int? after) {
    return Column(
      children: [
        Text(meal, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
        const SizedBox(height: 4),
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              before != null ? '$before' : '-',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 13,
                color: _getReadingColor(before),
              ),
            ),
            const Text(' / ', style: TextStyle(color: Colors.grey, fontSize: 11)),
            Text(
              after != null ? '$after' : '-',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 13,
                color: _getReadingColor(after),
              ),
            ),
          ],
        ),
      ],
    );
  }
}
