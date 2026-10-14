=== WP Sheet Editor Automations ===
Contributors: wpsheeteditor
Tested up to: 6.7
Stable tag: 1.0.8
Requires at least: 5.0
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Changelog ==
= V 1.0.8 = 2024-10-24 =
* CHANGE - Small changes

= V 1.0.7 = 2024-07-31 =
* NEW - Added option to import from FTP servers
* CHANGE - Improve the background import of CSV files by URL
* CHANGE - Added self-healing mechanism to imports, to auto retry failed batches or stuck batches 15 minutes later. It only works with imports that were configured to also update existing items, to avoid creating duplicates.
* CHANGE - Allow to schedule exports using filters that returned zero rows, because they might return rows in the future.
* CHANGE - Allow to schedule bulk edits using filters that returned zero rows, because they might return rows in the future.

= V 1.0.6 - 2024-03-21 =
* FIX - When we edit an export job, it is deleting the connection to the current Google Sheet file, so the next time it runs it creates a new Google Sheet instead of updating the existing Google Sheet
* FIX - Multiple issues that happened when we edited a scheduled job
* FIX - Added compatibility with Mailpoet's action scheduler

= V 1.0.5 - 2024-01-24 =
* FIX - The background batches throw an error when they're executed by the "action scheduler high performance" plugin
* FIX - When a background batch fails and we reschedule to retry later, it keeps rescheduling every future batch

= V 1.0.4 - 2023-12-12 =
* CHANGE - General improvements
* FIX - Permissions error in the middle of large scheduled exports/imports
* FIX - Sometimes the advanced edit of a job breaks
* FIX - Sometimes the advanced edit of a job creates a duplicate job

= V 1.0.3 - 2023-09-11 =
* FIX - UI issues when editing scheduled exports

= V 1.0.2 - 2023-07-29 =
* FIX - Small bug fixes

= V 1.0.1 - 2023-07-19 =
* CHANGE - Updated spanish translations

= V 1.0.0 - 2023-07-19 =
* Initial release
