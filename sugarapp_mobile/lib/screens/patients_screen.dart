import 'package:flutter/material.dart';
import '../models/patient_model.dart';
import '../services/api_service.dart';

class PatientsScreen extends StatefulWidget {
  final VoidCallback? onActiveChanged;

  const PatientsScreen({super.key, this.onActiveChanged});

  @override
  State<PatientsScreen> createState() => _PatientsScreenState();
}

class _PatientsScreenState extends State<PatientsScreen> {
  List<PatientModel> _patients = [];
  int? _activeId;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _fetchPatients();
  }

  Future<void> _fetchPatients() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final list = await ApiService.getPatients();
      final active = await ApiService.getActivePatientId();
      if (mounted) {
        setState(() {
          _patients = list;
          _activeId = active ?? (list.isNotEmpty ? list.first.id : null);
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

  Future<void> _setActive(int patientId) async {
    await ApiService.setActivePatientId(patientId);
    setState(() {
      _activeId = patientId;
    });
    widget.onActiveChanged?.call();
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Active patient updated!')),
      );
    }
  }

  void _showAddPatientDialog() {
    final formKey = GlobalKey<FormState>();
    final nameCtrl = TextEditingController();
    final ageCtrl = TextEditingController();
    final phoneCtrl = TextEditingController();
    final targetLowCtrl = TextEditingController(text: '80');
    final targetHighCtrl = TextEditingController(text: '180');
    final morningDoseCtrl = TextEditingController(text: '30');
    final nightDoseCtrl = TextEditingController(text: '30');
    String diabetesType = 'type2';
    String gender = 'male';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setModalState) => Padding(
          padding: EdgeInsets.only(
            left: 20,
            right: 20,
            top: 20,
            bottom: MediaQuery.of(ctx).viewInsets.bottom + 20,
          ),
          child: SingleChildScrollView(
            child: Form(
              key: formKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Add New Patient',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: nameCtrl,
                    decoration: const InputDecoration(
                      labelText: 'Patient Full Name',
                      border: OutlineInputBorder(),
                    ),
                    validator: (v) => v == null || v.trim().isEmpty ? 'Required' : null,
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: ageCtrl,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                            labelText: 'Age',
                            border: OutlineInputBorder(),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          value: gender,
                          decoration: const InputDecoration(
                            labelText: 'Gender',
                            border: OutlineInputBorder(),
                          ),
                          items: const [
                            DropdownMenuItem(value: 'male', child: Text('Male')),
                            DropdownMenuItem(value: 'female', child: Text('Female')),
                            DropdownMenuItem(value: 'other', child: Text('Other')),
                          ],
                          onChanged: (val) {
                            if (val != null) setModalState(() => gender = val);
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextFormField(
                    controller: phoneCtrl,
                    keyboardType: TextInputType.phone,
                    decoration: const InputDecoration(
                      labelText: 'Phone Number (Optional)',
                      border: OutlineInputBorder(),
                    ),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    value: diabetesType,
                    decoration: const InputDecoration(
                      labelText: 'Diabetes Type',
                      border: OutlineInputBorder(),
                    ),
                    items: const [
                      DropdownMenuItem(value: 'type1', child: Text('Type 1')),
                      DropdownMenuItem(value: 'type2', child: Text('Type 2')),
                      DropdownMenuItem(value: 'gestational', child: Text('Gestational')),
                      DropdownMenuItem(value: 'prediabetes', child: Text('Pre-diabetes')),
                    ],
                    onChanged: (val) {
                      if (val != null) setModalState(() => diabetesType = val);
                    },
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: targetLowCtrl,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                            labelText: 'Target Low (mg/dL)',
                            border: OutlineInputBorder(),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: TextFormField(
                          controller: targetHighCtrl,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                            labelText: 'Target High (mg/dL)',
                            border: OutlineInputBorder(),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),
                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0F766E),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                    ),
                    onPressed: () async {
                      if (!formKey.currentState!.validate()) return;
                      Navigator.pop(ctx);
                      setState(() => _isLoading = true);

                      try {
                        final newP = await ApiService.addPatient({
                          'name': nameCtrl.text.trim(),
                          'age': int.tryParse(ageCtrl.text),
                          'gender': gender,
                          'phone': phoneCtrl.text.trim().isEmpty ? null : phoneCtrl.text.trim(),
                          'diabetes_type': diabetesType,
                          'target_low': int.tryParse(targetLowCtrl.text) ?? 80,
                          'target_high': int.tryParse(targetHighCtrl.text) ?? 180,
                          'usual_morning_dose': double.tryParse(morningDoseCtrl.text) ?? 30.0,
                          'usual_night_dose': double.tryParse(nightDoseCtrl.text) ?? 30.0,
                        });
                        await _setActive(newP.id);
                        _fetchPatients();
                      } catch (e) {
                        setState(() => _isLoading = false);
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
                          );
                        }
                      }
                    },
                    child: const Text('Add Patient', style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Managed Patients'),
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
                      ElevatedButton(onPressed: _fetchPatients, child: const Text('Retry')),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: const EdgeInsets.all(16),
                  itemCount: _patients.length,
                  itemBuilder: (context, index) {
                    final p = _patients[index];
                    final isActive = p.id == _activeId;

                    return Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isActive ? const Color(0xFF0F766E) : const Color(0xFFE2E8F0),
                          width: isActive ? 2 : 1,
                        ),
                      ),
                      child: ListTile(
                        leading: CircleAvatar(
                          backgroundColor: isActive
                              ? const Color(0xFF0F766E)
                              : const Color(0xFF0F766E).withOpacity(0.12),
                          foregroundColor: isActive ? Colors.white : const Color(0xFF0F766E),
                          child: Text(
                            p.name.isNotEmpty ? p.name[0].toUpperCase() : 'P',
                            style: const TextStyle(fontWeight: FontWeight.bold),
                          ),
                        ),
                        title: Text(
                          p.name,
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                        ),
                        subtitle: Text(
                          '${p.diabetesType?.toUpperCase()} | Target: ${p.targetLow}-${p.targetHigh} mg/dL',
                          style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                        ),
                        trailing: isActive
                            ? const Chip(
                                label: Text('Active', style: TextStyle(color: Colors.white, fontSize: 11)),
                                backgroundColor: Color(0xFF0F766E),
                              )
                            : OutlinedButton(
                                onPressed: () => _setActive(p.id),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF0F766E),
                                  side: const BorderSide(color: Color(0xFF0F766E)),
                                ),
                                child: const Text('Select'),
                              ),
                      ),
                    );
                  },
                ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF0F766E),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.person_add),
        label: const Text('New Patient'),
        onPressed: _showAddPatientDialog,
      ),
    );
  }
}
