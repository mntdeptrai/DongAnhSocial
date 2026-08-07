# Flutter ProGuard Rules

# Keep Flutter main activity and native services
-keep class com.donganh.social.MainActivity { *; }
-keep class com.donganh.social.BackgroundPollingService { *; }
-keep class com.donganh.social.** { *; }

# Keep Flutter Engine and embedding classes
-keep class io.flutter.app.** { *; }
-keep class io.flutter.plugin.** { *; }
-keep class io.flutter.util.** { *; }
-keep class io.flutter.view.** { *; }
-keep class io.flutter.embedding.** { *; }
-keep class io.flutter.provider.** { *; }
-keep class io.flutter.plugin.editing.** { *; }

# Suppress R8 missing class warnings for Play Store deferred components
-dontwarn com.google.android.play.core.**
-dontwarn io.flutter.embedding.engine.deferredcomponents.**

# Keep native methods
-keepclasseswithmembernames class * {
    native <methods>;
}

# Keep generated plugin registrants
-keep class io.flutter.plugins.** { *; }
