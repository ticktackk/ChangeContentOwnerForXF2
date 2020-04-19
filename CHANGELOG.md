CHANGELOG
==========================

## 2.0.11 (`2001170`)

- **Fix:** When changing content owner attachment owner is not updated (#61)
  - **On upgrade:** Will make an attempt to rebuild attachment owners

## 2.0.10 (`2001070`)

- **Fix:** Call to undefined method getNewTimestamp (#57)

## 2.0.9 (`2000970`)

- **Fix:** Undefined index exception thrown when using inline moderation and invalid date is set (#52)
- **Fix:** In-line moderation does not respect thread date rules (#53)
- **Change:** Removed permission check if the user has permission to change owner or date from services (#54)
- **Change:** Set event hint for post add-on install code event listener (#55)

## 2.0.8 (`2000870`)

- **Fix:** Time interval is applied on first content (#49)

## 2.0.7 (`2000770`)

- **Misc:** Tweaked how time interval is applied for better control extending
- **Fix:** Moderator log action length can be higher than 25 characters (#47)
  - **On upgrade:** Will make an attempt to rename actions to new format.

## 2.0.6 (`2000670`)

- **Fix:** News feed event date does not update for the expected action (#44)
  - **On upgrade:** Will make an attempt to fix wrong event date for likes/reactions and insert events.

## 2.0.5 (`2000570`)

- **Fix:** Bulk updating can cause duplicate primary key exception (#41)

## 2.0.4 (`2000470`)

- **Fix:** User posts table is not updated on changing owner (#38)

## 2.0.3 (`2000370`)

- **Fix:** Content count value will be out of sync (#35)
- **Fix:** Posts count for user is increased twice (#33)
- **Fix:** Does not respect "Count messages posted in this forum towards user total" (#32)

**Note:** It is advised to run "Rebuild threads" with "Rebuild position and post counters" checked from Admin CP > Tools > Rebuild caches for small forums. If you this add-on a big forum, you'll be required to run the following command:

```shell script
php <path to xenforo installation>/cmd.php xf-rebuild:threads --position_rebuild
```

## 2.0.2 (`2000270`)

- **Fix:** Moderator log is generated even without owner or date changes (#28)
- **Fix:** Moderator actions are logged twice (#29) 
- **Fix:** Time bumping not being logged if nothing else was changed
- **Fix:** Added missing moderator log phrases #30
- **Removed:** Old 1.x moderator log phrases that are no longer used (#31) 
* On upgrade:
  * Will make an attempt to delete moderator logs that do not have any valid information

## 2.0.1 (`2000170`)

- **Fix:** Editor service trait doesn't return validation errors as it should

## 2.0.0 (`2000070`)

- **Change:** Use base XF app class instead of public app
- **Change:** Tweaked template modifications and class extensions
- **Fix:** Logic exception thrown when editing content

## 2.0.0 Release Candidate 4 (`2000054`)

- **Change:** Lowered minimum PHP requirement to 7.1
- **Change:** Added calendar extension to required extensions for installing the add-on
- **Fix:** Do not perform validations when using inline moderation (#25)

## 2.0.0 Release Candidate 3 (`2000053`)

- **New:** Improved the how content date will be set
- **Change:** Lowered minimum PHP version requirement
- **Change:** Updated phrases to match with general inline moderation
- **Change:** Do not allow changing content owner to the current owner
- **Fix:** `xf_thread_user_post` table not being updated when thread owner is changed
- **Fix:** Use `forceSet` to set user id when setting user id of a fallback user entity
- **Fix:** When changing profile post comment date, first comment date and last comment date were not compared correctly
- **Fix:** Thread date not being compared correctly when changing post date
- **Fix:** Changing thread owner does not update reactions
- **Fix:** Added missing phrases
* General code improvements and clean up

## 2.0.0 Release Candidate 2 (`2000052`)

- **Change:** Hour drop down when changing content date will now use hour format used in language
- **Fix:** Thread start not being updated (again)

## 2.0.0 Release Candidate 1 (`2000051`)

- **Fix:** Can change content date failing when using inline moderation
- **Fix:** New owner not being applied via inline moderation
- **Change:** Allow showing time intervals from `public:tckChangeContentOwner_macros:change_rows` macro

## 2.0.0 Alpha 3 (`2000013`)

- **New:** Allow changing seconds of the content
- **New:** Allow setting more specific time interval when changing time
- **Change:** New date will now be populated with existing content date outside in-line moderation
- **Fix:** User will receive "You must change the content owner or the date of this Thread." when editing content 
- **Fix:** Post dates can be older than first post date

## 2.0.0 Alpha 2 (`2000012`)

- **Fix:** First post not being changed when changing thread owner
- **Fix:** Returning invalid date for thread

## 2.0.0 Alpha 1 (`2000011`)

- **New:** Added support for profile post comment
- **New:** Ability to change content date
- **Change:** The entire add-on has been rewritten to be more flexible
- **Change:** Minimum PHP version has been increased to 7.3.0
- **Change:** More consistent author vs. owner

## 1.1.4 (`1010470`)

- **Fix**: Change author link does not move to the "..." menu in responsive mode

## 1.1.4 (`1010470`)

- **Fix**: Change author link does not move to the "..." menu in responsive mode

## 1.1.3 (`1010370`)

- **Fix:** TypeError is throw when attempting to change author of content with deleted user
- **Fix:** Do not allow changing first post author

## 1.1.2 (`1010270`)

- **Fix:** Message count not being updated
- **Fix:** News feed not being updated

## 1.1.1 (`1010170`)

- **Fix:** Change author would not show for XenForo Media Gallery 2.1+
- **Fix:** Attempted to get last comment id from the album which does not exist
- **Change:** Move "Change comment author" to the end

## 1.1.0 (`1010070`)

- **New:** Added support for removing likes/reactions upon changing the content owner
- **Change:** Increased the minimum XF requirement to 2.0.10

## 1.0.1 (`1000170`)

- **Fix:** Album last comment user id not being set correctly
- **Fix:** Checking for wrong permission in `XFMG:MediaItem` entity

## 1.0.0 (`1000070`)

- **Fix:** Profile user was used as old author instead of profile post author (since v1.0.0 Beta 2)
- **Fix:** Allow changing content owner if the original owner was a guest

## 1.0.0 Beta 3 (`1000033`)

- **Fix:** Class name was not case sensitive

## 1.0.0 Beta 2 (`1000032`)

- **Fix:** `Error: Call to undefined method XFRM\InlineMod\ResourceItem::getActions()`

## 1.0.0 Beta 1 (`1000031`)

- **New:** Added "Change author/owner..." to inline moderator tools for
  - Thread
  - Post
  - Profile Post
  - Media Item (XenForo Media Gallery)
  - Album (XenForo Media Gallery)
  - Comment (XenForo Media Gallery)
- **Changed:** All services were re-written to make use of `ValidateAndSavableTrait`

**Notices:**
- This version of the add-on includes a workaround for a bug XenForo 2.0.4 and early versions relating to in-line moderations

## 1.0.0 Alpha 4 (`1000014`)

- **New:** Added support for moderator logging

## 1.0.0 Alpha 3 (`1000013`)

- **Change:** Move "Changed album owner" to the bottom under "More options" menu 
- **Change:** Move "Changed media author" to the bottom under "More options" menu 
- **Change:** Move "Change album owner" to the end of macros
- **Change:** Move "Change comment author" to the end of macros
- **Change:** Move "Change media author" to the end of macros
- **Change:** Move "Change post author" to the end of macros

## 1.0.0 Alpha 2 (`1000012`)

 - **Change:** Template name is now `changeContentOwner_xfmg_album_change_owner` instead of `changeContentOwner_xfmg_album_change_author`
 - **Change:** Updated phrases
 - **Fix** Redirect back to the post after changing post author
 - **Fix:** Show overlay instead of redirecting when changing comment author

## 1.0.0 Alpha 1 (`1000011`)

First alpha release.