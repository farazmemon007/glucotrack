import 'package:flutter/material.dart';
import 'screens/login_screen.dart';
import 'screens/main_nav_screen.dart';
import 'services/api_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final token = await ApiService.getToken();
  runApp(SugarApp(isLoggedIn: token != null && token.isNotEmpty));
}

class SugarApp extends StatelessWidget {
  final bool isLoggedIn;

  const SugarApp({super.key, required this.isLoggedIn});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'SugarApp Mobile',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: const Color(0xFF0F766E),
          primary: const Color(0xFF0F766E),
          secondary: const Color(0xFF14B8A6),
          surface: Colors.white,
        ),
        scaffoldBackgroundColor: const Color(0xFFF8FAFC),
        appBarTheme: const AppBarTheme(
          backgroundColor: Colors.white,
          foregroundColor: Color(0xFF1E293B),
          elevation: 0.5,
          centerTitle: false,
        ),
      ),
      home: isLoggedIn ? const MainNavigationScreen() : const LoginScreen(),
    );
  }
}
