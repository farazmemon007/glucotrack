import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../models/patient_model.dart';
import '../services/api_service.dart';

class ReportsScreen extends StatefulWidget {
  final PatientModel patient;

  const ReportsScreen({super.key, required this.patient});

  @override
  State<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportsScreenState extends State<ReportsScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  int _selectedDays = 30;
  Map<String, dynamic> _stats = {};

  @override
  void initState() {
    super.initState();
    _fetchStats();
  }

  Future<void> _fetchStats() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final stats = await ApiService.getStats(
        widget.patient.id,
        days: _selectedDays,
      );
      if (mounted) {
        setState(() {
          _stats = stats;
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

  void _showPdfDialog() async {
    final now = DateTime.now();
    final pdfUrl = await ApiService.getPdfDownloadUrl(
      widget.patient.id,
      month: now.month,
      year: now.year,
    );

    if (!mounted) return;

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Row(
          children: const [
            Icon(Icons.picture_as_pdf, color: Colors.red),
            SizedBox(width: 8),
            Text('Export PDF Report'),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Official Sugar Log PDF for ${widget.patient.name} (${DateFormat('MMMM yyyy').format(now)}):',
              style: const TextStyle(fontSize: 13),
            ),
            const SizedBox(height: 12),
            SelectableText(
              pdfUrl,
              style: const TextStyle(fontSize: 12, color: Color(0xFF0F766E), fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            const Text(
              'You can open this link in any browser to view or print the doctor log sheet.',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
        actions: [
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF0F766E), foregroundColor: Colors.white),
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Close'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final avg = _stats['average'] ?? '--';
    final inRange = _stats['time_in_range_percent'] ?? 0;
    final highPercent = _stats['high_percent'] ?? 0;
    final lowPercent = _stats['low_percent'] ?? 0;
    final totalReadings = _stats['total_readings'] ?? 0;
    final mealAvgs = _stats['meal_averages'] as Map<String, dynamic>? ?? {};
    final needsAttention = _stats['needs_attention'] as List<dynamic>? ?? [];

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text('${widget.patient.name} - Reports'),
        backgroundColor: Colors.white,
        elevation: 0.5,
        actions: [
          IconButton(
            icon: const Icon(Icons.picture_as_pdf, color: Colors.redAccent),
            tooltip: 'Download PDF Report',
            onPressed: _showPdfDialog,
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
                      ElevatedButton(onPressed: _fetchStats, child: const Text('Retry')),
                    ],
                  ),
                )
              : RefreshIndicator(
                  onRefresh: _fetchStats,
                  color: const Color(0xFF0F766E),
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(16.0),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // Period Selector (7 days vs 30 days)
                        Row(
                          children: [
                            _buildFilterChip('Last 7 Days', 7),
                            const SizedBox(width: 8),
                            _buildFilterChip('Last 30 Days', 30),
                            const Spacer(),
                            OutlinedButton.icon(
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.red.shade700,
                                side: BorderSide(color: Colors.red.shade300),
                                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              ),
                              icon: const Icon(Icons.picture_as_pdf, size: 16),
                              label: const Text('PDF Sheet', style: TextStyle(fontSize: 12)),
                              onPressed: _showPdfDialog,
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),

                        // Main Average Card
                        Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(
                            gradient: const LinearGradient(
                              colors: [Color(0xFF0F766E), Color(0xFF134E4A)],
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                            ),
                            borderRadius: BorderRadius.circular(18),
                            boxShadow: [
                              BoxShadow(
                                color: const Color(0xFF0F766E).withValues(alpha: 0.25),
                                blurRadius: 10,
                                offset: const Offset(0, 4),
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
                                    'PERIOD AVERAGE',
                                    style: TextStyle(
                                      color: Colors.white70,
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      letterSpacing: 1,
                                    ),
                                  ),
                                  Text(
                                    'Target: ${widget.patient.targetLow}–${widget.patient.targetHigh} mg/dL',
                                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 10),
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.baseline,
                                textBaseline: TextBaseline.alphabetic,
                                children: [
                                  Text(
                                    '$avg',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 44,
                                      fontWeight: FontWeight.w900,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  const Text(
                                    'mg/dL',
                                    style: TextStyle(color: Colors.white70, fontSize: 16),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Text(
                                'Based on $totalReadings recorded sugar tests',
                                style: const TextStyle(color: Colors.white, fontSize: 13),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Stats Breakdown Grid
                        Row(
                          children: [
                            Expanded(
                              child: _buildMetricTile(
                                label: 'In Target',
                                value: '$inRange%',
                                color: const Color(0xFF10B981),
                                icon: Icons.check_circle_outline,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _buildMetricTile(
                                label: 'High Sugar',
                                value: '$highPercent%',
                                color: const Color(0xFFEF4444),
                                icon: Icons.arrow_upward,
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: _buildMetricTile(
                                label: 'Low Sugar',
                                value: '$lowPercent%',
                                color: const Color(0xFFF59E0B),
                                icon: Icons.arrow_downward,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 18),

                        // Meal-wise Averages
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: const Color(0xFFE2E8F0)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Meal Averages',
                                style: TextStyle(
                                  fontSize: 15,
                                  fontWeight: FontWeight.bold,
                                  color: Color(0xFF1E293B),
                                ),
                              ),
                              const SizedBox(height: 14),
                              _buildMealAvgRow('Nashta (Breakfast)', mealAvgs['nashta']?['average']),
                              const Divider(height: 16),
                              _buildMealAvgRow('Lunch', mealAvgs['lunch']?['average']),
                              const Divider(height: 16),
                              _buildMealAvgRow('Dinner', mealAvgs['dinner']?['average']),
                            ],
                          ),
                        ),
                        const SizedBox(height: 18),

                        // Needs Attention / Doctor Review Alerts
                        if (needsAttention.isNotEmpty) ...[
                          const Text(
                            'Needs Attention (Doctor Review)',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1E293B),
                            ),
                          ),
                          const SizedBox(height: 10),
                          ...needsAttention.map((item) {
                            final isHigh = item['badge_type'] == 'high';
                            return Container(
                              margin: const EdgeInsets.only(bottom: 8),
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: isHigh ? Colors.red.shade50 : const Color(0xFFFEF3C7),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: isHigh ? Colors.red.shade200 : Colors.amber.shade300,
                                ),
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    isHigh ? Icons.warning_rounded : Icons.info_outline,
                                    color: isHigh ? Colors.red : Colors.amber.shade800,
                                    size: 22,
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          item['title'] ?? '',
                                          style: TextStyle(
                                            fontWeight: FontWeight.bold,
                                            fontSize: 13,
                                            color: isHigh ? Colors.red.shade900 : Colors.amber.shade900,
                                          ),
                                        ),
                                        Text(
                                          '${item['date']} • ${item['detail']}',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: isHigh ? Colors.red.shade700 : Colors.amber.shade800,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                        ],
                      ],
                    ),
                  ),
                ),
    );
  }

  Widget _buildFilterChip(String label, int days) {
    final isSelected = _selectedDays == days;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: const Color(0xFF0F766E),
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : const Color(0xFF1E293B),
        fontWeight: FontWeight.bold,
        fontSize: 12,
      ),
      onSelected: (val) {
        if (val) {
          setState(() {
            _selectedDays = days;
          });
          _fetchStats();
        }
      },
    );
  }

  Widget _buildMetricTile({
    required String label,
    required String value,
    required Color color,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          Icon(icon, color: color, size: 20),
          const SizedBox(height: 6),
          Text(
            value,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: color,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
          ),
        ],
      ),
    );
  }

  Widget _buildMealAvgRow(String meal, dynamic avgVal) {
    final valStr = avgVal != null ? '$avgVal mg/dL' : 'No records';
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(meal, style: const TextStyle(fontSize: 14, color: Color(0xFF334155))),
        Text(
          valStr,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: Color(0xFF0F766E),
          ),
        ),
      ],
    );
  }
}
