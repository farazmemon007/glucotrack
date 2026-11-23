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
  int _selectedFilterDays = 30; // 7, 30, or all

  @override
  void initState() {
    super.initState();
    _fetchHistory();
  }

  @override
  void didUpdateWidget(LogsHistoryScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.patient.id != widget.patient.id) {
      _fetchHistory();
    }
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

  void _openEdit(DailyLogModel log) async {
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
  }

  void _deleteLog(DailyLogModel log) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Log'),
        content: Text('Delete reading for ${log.logDate}?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: Colors.red, foregroundColor: Colors.white),
            onPressed: () async {
              Navigator.pop(ctx);
              setState(() => _isLoading = true);
              try {
                await ApiService.deleteLog(widget.patient.id, log.id);
                _fetchHistory();
              } catch (e) {
                setState(() => _isLoading = false);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
                  );
                }
              }
            },
            child: const Text('Delete'),
          ),
        ],
      ),
    );
  }

  Color _getReadingColor(int? sugar) {
    if (sugar == null) return Colors.grey;
    if (sugar < widget.patient.targetLow) return const Color(0xFFF59E0B);
    if (sugar > widget.patient.targetHigh) return const Color(0xFFEF4444);
    return const Color(0xFF10B981);
  }

  @override
  Widget build(BuildContext context) {
    final filteredLogs = _selectedFilterDays == 0
        ? _logs
        : _logs.take(_selectedFilterDays).toList();

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text('${widget.patient.name} - History'),
        backgroundColor: Colors.white,
        elevation: 0.5,
        foregroundColor: const Color(0xFF1E293B),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Color(0xFF0F766E)),
            onPressed: _fetchHistory,
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
                      ElevatedButton(onPressed: _fetchHistory, child: const Text('Retry')),
                    ],
                  ),
                )
              : Column(
                  children: [
                    // Filter Chips: 7 days, 30 days, All
                    Container(
                      color: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      child: Row(
                        children: [
                          const Text('Filter: ', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
                          const SizedBox(width: 8),
                          ChoiceChip(
                            label: const Text('7 Days'),
                            selected: _selectedFilterDays == 7,
                            selectedColor: const Color(0xFF0F766E),
                            labelStyle: TextStyle(
                              color: _selectedFilterDays == 7 ? Colors.white : Colors.black87,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                            onSelected: (val) {
                              if (val) setState(() => _selectedFilterDays = 7);
                            },
                          ),
                          const SizedBox(width: 8),
                          ChoiceChip(
                            label: const Text('30 Days'),
                            selected: _selectedFilterDays == 30,
                            selectedColor: const Color(0xFF0F766E),
                            labelStyle: TextStyle(
                              color: _selectedFilterDays == 30 ? Colors.white : Colors.black87,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                            onSelected: (val) {
                              if (val) setState(() => _selectedFilterDays = 30);
                            },
                          ),
                          const SizedBox(width: 8),
                          ChoiceChip(
                            label: const Text('All Logs'),
                            selected: _selectedFilterDays == 0,
                            selectedColor: const Color(0xFF0F766E),
                            labelStyle: TextStyle(
                              color: _selectedFilterDays == 0 ? Colors.white : Colors.black87,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                            onSelected: (val) {
                              if (val) setState(() => _selectedFilterDays = 0);
                            },
                          ),
                        ],
                      ),
                    ),
                    const Divider(height: 1),

                    // Logs List
                    Expanded(
                      child: filteredLogs.isEmpty
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
                                  const SizedBox(height: 12),
                                  ElevatedButton.icon(
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: const Color(0xFF0F766E),
                                      foregroundColor: Colors.white,
                                    ),
                                    icon: const Icon(Icons.add),
                                    label: const Text('Add Reading'),
                                    onPressed: () async {
                                      final res = await Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) => AddLogScreen(patient: widget.patient),
                                        ),
                                      );
                                      if (res == true) _fetchHistory();
                                    },
                                  ),
                                ],
                              ),
                            )
                          : RefreshIndicator(
                              onRefresh: _fetchHistory,
                              color: const Color(0xFF0F766E),
                              child: ListView.builder(
                                padding: const EdgeInsets.all(16),
                                itemCount: filteredLogs.length,
                                itemBuilder: (context, index) {
                                  final log = filteredLogs[index];
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
                                          color: Colors.black.withValues(alpha: 0.02),
                                          blurRadius: 6,
                                          offset: const Offset(0, 2),
                                        ),
                                      ],
                                    ),
                                    child: Padding(
                                      padding: const EdgeInsets.all(14),
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          // Header with Date, Doctor Badge, and Action Buttons (Edit / Delete)
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
                                              Row(
                                                children: [
                                                  if (log.needsCheck)
                                                    Container(
                                                      margin: const EdgeInsets.only(right: 6),
                                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                                      decoration: BoxDecoration(
                                                        color: Colors.red.shade50,
                                                        borderRadius: BorderRadius.circular(6),
                                                      ),
                                                      child: const Text(
                                                        'Check',
                                                        style: TextStyle(
                                                          color: Colors.red,
                                                          fontSize: 11,
                                                          fontWeight: FontWeight.bold,
                                                        ),
                                                      ),
                                                    ),
                                                  // Explicit Edit Button
                                                  IconButton(
                                                    icon: const Icon(Icons.edit_outlined, size: 20, color: Color(0xFF0F766E)),
                                                    tooltip: 'Edit reading',
                                                    constraints: const BoxConstraints(),
                                                    padding: const EdgeInsets.all(4),
                                                    onPressed: () => _openEdit(log),
                                                  ),
                                                  const SizedBox(width: 4),
                                                  // Delete Button
                                                  IconButton(
                                                    icon: const Icon(Icons.delete_outline, size: 20, color: Colors.redAccent),
                                                    tooltip: 'Delete reading',
                                                    constraints: const BoxConstraints(),
                                                    padding: const EdgeInsets.all(4),
                                                    onPressed: () => _deleteLog(log),
                                                  ),
                                                ],
                                              ),
                                            ],
                                          ),
                                          const Divider(height: 18),

                                          // Meal Breakdown (Nashta, Lunch, Dinner)
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

                                          // Tap to edit prompt
                                          const SizedBox(height: 8),
                                          InkWell(
                                            onTap: () => _openEdit(log),
                                            child: const Center(
                                              child: Text(
                                                'Tap to edit reading details ✏️',
                                                style: TextStyle(fontSize: 11, color: Color(0xFF0F766E), fontWeight: FontWeight.w600),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  );
                                },
                              ),
                            ),
                    ),
                  ],
                ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: const Color(0xFF0F766E),
        foregroundColor: Colors.white,
        child: const Icon(Icons.add),
        onPressed: () async {
          final res = await Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => AddLogScreen(patient: widget.patient),
            ),
          );
          if (res == true) _fetchHistory();
        },
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
