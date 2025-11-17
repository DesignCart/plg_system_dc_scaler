=== DC Scaler ===
Lightweight Joomla performance optimizer for CSS & JavaScript merging, minification and caching.

== Description ==

DC Scaler is a high-performance Joomla system plugin that automatically:
• collects all CSS and JS assets loaded by Joomla and extensions  
• merges them into single optimized files  
• minifies CSS and JavaScript for faster delivery  
• replaces original assets with the optimized versions  
• stores results in cache to deliver instant page loads  
• supports exclusion rules for both CSS and JS  
• supports automatic cache lifetime refresh  
• supports purge-on-next-request (manual invalidation)  

This plugin is ideal for Joomla websites that need frontend speed improvements without complicated configuration or server-side tools.

== Features ==

• Full CSS merging & minification  
• Full JS merging & minification (Shrink minifier included)  
• Auto-regeneration based on lifetime (minutes)  
• Optional purge on next request  
• Exclusion lists for CSS and JS (full paths, partial matches)  
• Zero dependencies — works without Composer  
• Safe for all Joomla templates and extensions  
• Multi-environment friendly (local, staging, production)

== Installation ==

1. Install the ZIP package via Joomla Extension Manager.  
2. Enable the plugin "System – DC Scaler".  
3. Configure:
   • Enable CSS optimization  
   • Enable JS optimization  
   • Exclusion rules (optional)  
   • Cache lifetime  
   • Purge on next request  

== Configuration ==

Exclude CSS:  
Add one path per line (full or partial), e.g.:  
