import 'package:flutter/material.dart';
import '../models/patient_model.dart';
import '../services/api_service.dart';
import 'home_screen.dart';
import 'logs_history_screen.dart';
import 'patients_screen.dart';
import 'reports_screen.dart';

class MainNavigationScreen extends StatefulWidget {
  final int initialIndex;

  const MainNavigationScreen({super.key, this.initialIndex = 0});

  @override
  State<MainNavigationScreen> createState() => _MainNavigationScreenState();
}

class _MainNavigationScreenState extends State<MainNavigationScreen> {
  int _currentIndex = 0;
  List<PatientModel> _patients = [];
  PatientModel? _activePatient;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _loadPatientData();
  }

  Future<void> _loadPatientData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final list = await ApiService.getPatients();
      if (list.isEmpty) {
        throw Exception('No patients found. Please add a patient.');
      }

      final savedId = await ApiService.getActivePatientId();
      PatientModel active = list.first;
      if (savedId != null) {
        active = list.firstWhere((p) => p.id == savedId, orElse: () => list.first);
      }

      if (mounted) {
        setState(() {
          _patients = list;
          _activePatient = active;
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

  void _onPatientChanged(PatientModel newPatient) async {
    await ApiService.setActivePatientId(newPatient.id);
    setState(() {
      _activePatient = newPatient;
    });
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        body: Center(
          child: CircularProgressIndicator(color: Color(0xFF0F766E)),
        ),
      );
    }

    if (_errorMessage != null || _activePatient == null) {
      return Scaffold(
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(_errorMessage ?? 'Failed to load patient profile', style: const TextStyle(color: Colors.red)),
              const SizedBox(height: 12),
              ElevatedButton(onPressed: _loadPatientData, child: const Text('Retry')),
            ],
          ),
        ),
      );
    }

    final patient = _activePatient!;

    final screens = [
      HomeScreen(
        key: ValueKey('home_${patient.id}'),
        patient: patient,
        patients: _patients,
        onPatientChanged: _onPatientChanged,
        onNavigateToTab: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
      ),
      LogsHistoryScreen(
        key: ValueKey('history_${patient.id}'),
        patient: patient,
      ),
      ReportsScreen(
        key: ValueKey('reports_${patient.id}'),
        patient: patient,
      ),
      PatientsScreen(
        key: const ValueKey('patients_list'),
        onActiveChanged: () {
          _loadPatientData();
        },
      ),
    ];

    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: screens,
      ),
      bottomNavigationBar: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border(top: BorderSide(color: Colors.grey.shade200, width: 1)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 8,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: BottomNavigationBar(
          currentIndex: _currentIndex,
          onTap: (index) {
            setState(() {
              _currentIndex = index;
            });
          },
          type: BottomNavigationBarType.fixed,
          backgroundColor: Colors.white,
          selectedItemColor: const Color(0xFF0F766E),
          unselectedItemColor: const Color(0xFF64748B),
          selectedLabelStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
          unselectedLabelStyle: const TextStyle(fontSize: 11),
          elevation: 0,
          items: const [
            BottomNavigationBarItem(
              icon: Icon(Icons.home_outlined),
              activeIcon: Icon(Icons.home),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.history_outlined),
              activeIcon: Icon(Icons.history),
              label: 'History',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.bar_chart_outlined),
              activeIcon: Icon(Icons.bar_chart),
              label: 'Reports',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.people_outline),
              activeIcon: Icon(Icons.people),
              label: 'Patients',
            ),
          ],
        ),
      ),
    );
  }
}
