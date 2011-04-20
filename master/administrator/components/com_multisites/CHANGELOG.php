<?php
/**
 * @file       CHANGELOG.php
 * @brief      This document logs changes with a brief description
 *
 * @version    1.2.47
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2011 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

1. Changelog
------------
This is a non-exhaustive (but still near complete) changelog for Jms Multi-Sites.

-------------------- 1.2.47 [08-feb-2011] ---------------------
- Add compatibility with Joomla 1.6.0 stable.
  Filter the "installation/uninstall" processing to avoid install extension in a slave site
  that does not already exists in the master website.
  Also check avoid to delete the PHP code of the extension when uninstalling an extension
  from the back-end of a slave site.
- Add verification that a DB does not exists when creating a DB dynamically.
  In some case, Joomla can return a DB object that is not connected to any DB
  with success. The new check, verify that a DB ressource is created in Joomla.
- Bundled with Patch definition 1.2.53
  > Add JMS Tools (install) definitions for :
    - Affiliate Text Ads,
    - Attend JEvents,
    - AutoTweet, 
    - HikaShop, 
    - Newsletter (Joo Mailer Mailchimp Integration), 
    - Joo Mailer Mailchimp Signup
    - Joomlart extensions manager
    - JA Buletin,
    - JA MegaMenu,
    - JA News2 Module,
    - JA News Ticker Module,
    - JA News Frontpage Module
    - JA Tabs
    - JA Twitter
    - JA Bookmark
    - JA Disqus Debate Echo Plugin
    - JA Thumbnail
    - JA Popup
    - JA Section menu plugin
    - JA T3 Framework - Need patch
    - JA User Setting
    - JoomShopping
    - Nurte Facebook Like Button
    - OSE UPMan
    - RSComments!
    - RSMail
    - RSMembership!
    - WDBanners
  > Fix Joomla 1.5 sharing definition for:
    - Tienda
  > Add Joomla 1.5 sharing definition for:
    - HikaShop, 
    - Affiliate Text Ads

-------------------- 1.2.46 [13-jan-2011] ---------------------
- Add compatibility with Joomla 1.6.0 stable.
  It is no more compatible with joomla 1.6.0 alpha, beta, RC1
  Also bundled with Joomla 1.6.0 installation directory.
- Fix the name that allow adding patches definition in a plugin

- Bundled with Patch definition 1.2.52
  > Add patch for CBE (Community Builder Enhanced)
  > Modify the patches for Joomla 1.6.0 stable
  > Modify the patches for acesef 1.5.13 compatibility
  > Add JMS Tools (install) definitions for :
    - CBE, GCalendar, JoomGallery Treeview, 
      JSPT / XIPT / JomSocial Profile Types,
      VM Affiliate Tracking Module,
      WordPress
  > Add Joomla 1.5 sharing definition for:
    - CBE, JSPT, WordPress

-------------------- 1.2.45 [06-dec-2010] ---------------------
- Add compatibility with Joomla 1.6 RC1 and remove most of the patches that are no more
  required by the new Joomla 1.6 RC architecture that allow adding files to overwrite some functionalities.
  Also bundled with Joomla 1.6 RC1 installation directory
  
- Bundled with Patch definition 1.2.51
  > Add JMS Tools (install) definitions for :
    - RS Events, iJoomla SEO
  > Add Joomla 1.5 sharing definition for:
    - RS Events, RS Form

-------------------- 1.2.44 [06-dec-2010] ---------------------
- Add Joomla 1.6 compatibility for the "tool" menu.
  Now it is possible to install,share and uninstall extension from the "tool".
- Add possibility to disable the refresh icon to speed-up refresh of the list of slave site present in the manage site.
  The parameter MULTISITES_REFRESH_DISABLED is added to disable the count of tables present in each slave site.  
- Bundled with Joomla 1.6 beta 15 installation file.  
- Bundled with Patch definition 1.2.50
  > Add JMS Tools (install) definitions for :
    - FLEXIaccess,
    - HotelGuide,
    - jShareEasy,
    - JV-LinkDirectory,
    - JV-LinkExchanger

-------------------- 1.2.43 [15-nov-2010] ---------------------
- Force Alias Links created in lowercase
- Add the keyword {user_email}
- Bundled with Patch definition 1.2.49
  Add a patch for the All Video download script to compute the "document root" directory based on the deployed directory.
  The sitePath "document root" directory is not correct when using Symbolic Link.
  > Add JMS Tools (install) definitions for :
    - All Video, 
    - Nooku Framework, 
    - Ninja 1.5, 
    - Koowa system plugin

-------------------- 1.2.42 [05-nov-2010] ---------------------
- Add compatibility with Joomla 1.5.22
- Bundled with Patch definition 1.2.48
  Fix the existing patch that accept to share the sessions between subdomains or subdirectories
  to be compatible with Joomla 1.5.22
  > Add JMS Tools (install) definitions for :
    - Copyright Current Year, 
    - jDownloads, 
    - JW Tabs & Slides Module

-------------------- 1.2.41 [26-oct-2010] ---------------------
- Add partial Joomla 1.6 beta 12 compatibility.
- Bundled with Patch definition 1.2.47
  > Add JMS Tools (install) definitions for :
    - Tiendra
  > Add Joomla 1.5 sharing definition for:
    - Tiendra: 
      This is a release candidate.
      You may have a limitation when using reference to joomla articles that will not be shared, 
    - Joomla Estate Agency
      
-------------------- 1.2.40 [10-oct-2010] ---------------------
- Bundled with Patch definition 1.2.46
  > Add JMS Tools (install) definitions for :
    - Auctions, 
    - Restaurant Guide,
    - CodeCitation plugin,
    - Versioning Workflow
  > Add Joomla 1.5 sharing definition for:
    - Auctions (full sharing and only user sharing),
    - Restaurant Guide 
      with limitation on the Linked Articles that can not be used as the articles are not shared

-------------------- 1.2.39 [10-oct-2010] ---------------------
- Add partial compatibility with Joomla 1.6 beta 11
- Bundled with Patch definition 1.2.45
  > Add patch for Mobile Joomla
  > Add JMS Tools (install) definitions for :
    - CB Search plugin,
    - ai Sobi Search

-------------------- 1.2.38 [23-sep-2010] ---------------------
- Add partial compatibility with Joomla 1.6 beta 10
- Bundled with Patch definition 1.2.44
  > Add JMS Tools (install) definitions for :
    - AceSEF plugin,
    - Categories module, 
    - Joomdle, 
    - JoomFish SEF, 
    - Kunena 1.6, 
    - Scheduler
  > Add Joomla 1.5 sharing definition for:
    - Kunena 1.6
    - Scheduler
  > Add Joomla 1.6 sharing definition for:
    - Kunena 1.6

-------------------- 1.2.37 [12-sep-2010] ---------------------
- Improve the front-end website creation when SEF is enabled to avoid using the default
  Joomla SEF processing that might have wrong encoding / decoding of SEF.
  When using JoomFish and SEF enabled, it is recommended to install a fix
  concerning the HTML <base tag when # anchor are used.
  See http://www.metamodpro.com/software/jfsef for the download of the fix.
- Bundled with Patch definition 1.2.43
  > Update the AceSEF patch to be compatible with AceSEF version 1.5.x
  
-------------------- 1.2.36 [08-sep-2010] ---------------------
- Joomla 1.6 beta 9 compatibility.
  * installation language conversion to avoid convert leading and trailing quotes in _QQ_
  * Partially fix the problem relative to load of new layouts in the back-end
  * Fix some patches for Joomla 1.6
- Factory: Add internal routine to get the first domain name based on its site id
- Fix front-end website creation when SEF is enabled.
  The problem is due to standard Joomla 1.5 SEF processing that does not resolve correctly
  that URL to their aliases when parameters are present. It does not cleanup the parameters
  that are already present in the menu definition and therefore put URL that add the parameters twice.
  The result is that parameters become arrays instead of strings and are mis-understood.
  This mainly affect the parameters views and layout that might be present twices.
- Fix front-end layout selection on Joomla 1.6
- Bundled with Patch definition 1.2.42
  > Fix the patch concerning the Global Configuration in Joomla 1.6.
  > Add JMS Tools (install) definitions for :
    - Multisites Content Modules (NewsFlash and LatestNews),
    - Multisites Contact,
    - BreezingForms >= 1.7.2 (formelly Facile Forms),
    - Listbingo,
    - Projectfork,
    - RSTickets! Pro,
    - Community Builder Profile Pro + Magic Window,
    - Grid
  > Add sharing definition for:
    - Ignite Gallery,
    - Community Builder Profile Pro + Magic Window
    
-------------------- 1.2.35 [03-aug-2010] ---------------------
- Joomla 1.6 beta 6 compatibility to allow reset the database error number and message.
- Fix expiration URL to always be checked from the front-end.
  This consists in saving the slave site definition into the master index event when it is already expired.
- Remove a debug trace that were present in the front-end when the login (session) expired
- Use SEF url in the front-end website creation when editing a slave site.
- Bundled with Patch definition 1.2.41
  > Modify the SH404SEF patch to be complient with version 2.0.
    Some patches where moved into other files after they have refactor their code to use MVC.
  > Add JMS Tools (install) definitions for :
    - Multisites patches for Mighty,
    - Disqus Comment System for Joomla!,
    - Scribe, 
    - SEF Title Prefix and Suffix, 
    - some sh404SEF plugins,
    - Simply Links, 
    - WysiwygPro3

-------------------- 1.2.34 [24-jul-2010] ---------------------
- Remove one Joomla 1.6 database patch that is replaced by new MultisitesDatabase API that allow update
  the protected JDatabase table prefix.
- Improve Joomla 1.6 beta 5 language file conversion to use "_QQ_" for the quote character.
- Modify the "Site" element to autorize multiple selection and also make it compatible with Joomla 1.6 beta 5.
- Modify the "Layout" element to make it compatible with Joomla 1.6 beta 5.
- Make the front-end compatible with Joomla 1.6 beta 5.
- Fix a bug in JMS tools introduced in 1.2.33 concerning the action to share a component.
  The new possibility to exclude some share tables from a generic list were resulted in the impossibility
  to share the extension from the JMS Tools.
- Bundled with Joomla 1.5.20
- Bundled with Patch definition 1.2.40
  > Add JMS Tools (install) definitions for :
    - Ninja Content, Simple Caddy, Sourcerer, redEVENT, redFORM,
      Multisites Search plugins = MultisitesContent, MultisitesCategories, MultisitesSections;
  > Add sharing definition for:
    - Simple Caddy, redEVENT, redFORM.

-------------------- 1.2.33 [13-jul-2010] ---------------------
- Add Joomla 1.6 beta 5 compatibility (Menu is fixed and possibility to see the JMS tools - not yet perform actions).
- Add now the possibility to exclude some table in the sharing definition.
- Add check that alias is not already used by another slave site when creating a slave site from the front-end
- Bundled with Patch definition 1.2.39
  > Add patch for CssJsCompress.
  > Fix the patch for CB 1.2.3 that moved some code into another source
  > Fix the patch for the Sermon Speaker 3.4.1
  > Add JMS Tools (install) definitions for :
    - Annonces, CssJsCompress, Djf Acl, EstateAgent, jSecure Authentication, MooFAQ
      QuickContent, PU Arcade, AjaxChat, Add also some plugin for JomSocial 1.8.3
  > Add sharing definition for:
    - Annonces, JVideo, PU Arcade, AjaxChat
      JEvent with default excluded.
      
-------------------- 1.2.32 [21-jun-2010] ---------------------
- When updating an existing slave site, check if it was located in a flat structure to force this format in case where the letter tree is enabled.
- Fix the delete of a slave site when using the letter tree to avoid delete recursivelly all slave site under a give letter tree entry.
- Fix the computation of the list of sites when "getSites" is called from a slave site where "/multisites" is a link.
- Fix a bug introduced when implementing the "letter tree" that display all the list of website event when a filter is provided.
- Modify all the language files to replace some special characters by their html equivalent to avoid problem reading the language files
  in Joomla 1.6.0 that now use the parse_ini_file() function that have more restrictions
   ( &#40;
   ) &#41;
   { &#123;
   } &#125;
   [ &#91;
   ] &#93;
   " &#34;
- Remove a warning in the "installation" to hide a warning on set_time_limit() when the call to this function not allowed by a server
  that have the safe mode enabled.
- Add the possibility to UpdateStatus on All the websites.
- Joomla 1.6 compatibility to take in account the fact that now it does not return the reference to the models in a view.
- Bundled with Patch definition 1.2.38
  > Add Joomla 1.6.0 beta 2 compatibility.
  > Add JMS Tools (install) definitions for :
    - Joomla Flash Uploader, synk, Akeeba Backup
      Blue Flame Forms For Joomla, Extended Menu, JB FAQ, JB Slideshow
      JEV Location, Spec Images, JVideo!, JoomLoc
                             
    - Incompatibility with RocketThemes RokDownloadBundle version 1.0.1
      The new RokDownload component is package into a RokDownloadBundle 
      that install the RokDownload component as a core joomla component.
      JMS Multisites does not provide interface to install "core component"
      that normally are the one present when installing the joomla cms.
  > Add sharing definition for:
    * JoomLoc

-------------------- 1.2.31 [6-jun-2010] ---------------------
- Bundled with Patch definition 1.2.37 that fix a bug introduced in the patch 1.2.36 and that corrupted the joomla template manager.

-------------------- 1.2.30 [4-jun-2010] ---------------------
- Add keyword {site_id_letters} and call to plugin multisites onKeywordResolution to allow customer keyword processing.
- Give the possibility to create a special plugin to define external DBSharing and DBTable definitions that will complete the JMS Multisites one.
- Fix the detection that patches are already loaded to speedup the processing.
- Bundled with Patch Definition 1.2.33.
  > Modify several existing patches to take in account the new letter tree directory structure
  > Add JMS Tools (install) definitions for :
    - JS Testimonials

-------------------- 1.2.29 [30-may-2010] ---------------------
- Fix warning message reported by some "preg_split" and introduced when adding the PHP 5.3 compatibility
- Introduced the possibility to store the multisites configuration into a letter tree to reduce the number
  of folders in the "/multisites" directory and therefore speedup the OS when there are a lot of slave sites (>10,000 slaves).
- Add the possibility to add contributors patches. Call plugin multisites to defined the patches.
  Possibility to implement the functions coreFunctions2Backup() & files2Path().
- Add resolution of keywords {user_login} and {user_name}.
- Add a new function in the Util API to allow rebuild the Multistes master index.
- Give the possibility to disable the Top Level Domain (TLD) computation to speedup the Multisites processing
  when none of the slave sites contain domain with TLD composed of several words like co.uk co.za, co.au, ...
- Avoid replacing the "multisites.cfg.php" when already present. This files is only create at the first installation
  and based on the "multisites.cfg-dist.php" file.
- Add configuration parameters
  MULTISITES_TLD_PARSING   Give the possibility to disable the Top Level Domain (TLD) computation to speedup the Multisites processing
                           when none of the slave sites contain domain with TLD composed of several words like co.uk co.za, co.au, ...
  MULTISITES_LETTER_TREE   Use a letter tree to have the Multisites "Slave site" configuration and therefore reduce the number of files/folders at each level.
                           When not defined, it is assume this is false and use the default flat directory structure.
- FRONT-END
  > Add the possibility to redirect to a specific URL in case of error.
  > Add new "OnBeforeSave" Multisites plugin functions that is called before saving a front-end slave sites.
  > Add possibility to flag all the field in the front-end edit.
  > Add the possibilty to create a slave site with an anonymous users (not logged).
- Bundled with Patch Definition 1.2.35.
  Modify 'admin index' patch to take in account the new letter tree directory structure used when there are a lot of slave sites.
  > Add JMS Tools (install) definitions for :
    * QContact, Multisites Affiliate, GroupJive, JoomLeague, myApi, OSE Webmail
      Seo Links, Update Manager, VM Emails Manager, WebmapPlus
  > Add sharing definition for:
    * GroupJive  

-------------------- 1.2.28 [29-apr-2010] ---------------------
- Bundled with Joomla 1.5.17
- Bundled with Patch Definition 1.2.34.

-------------------- 1.2.27 [24-apr-2010] ---------------------
- Add PHP 5.3 compatibility and remove all the deprecated functions like ereg and split.
- Fix a PHP error when computing "TLD" (Top Level Domain) on shared extension.
- Bundled with Joomla 1.5.16
- Bundled with Patch Definition 1.2.33.
  > Add the fix concerning a bug introduced in Joomla 1.5.16 and that does not allow to login.
    See the joomla bug tracker published at
    http://joomlacode.org/gf/project/joomla/tracker/?action=TrackerItemEdit&tracker_item_id=20221
  > Add JMS Tools (install) definitions for :
    * Job Grok Application, Kuneri Mobile Joomla, ninjaXplorer
      Quick Jump Extended, WEBO Site SpeedUp, aiContactSafe, Rentalot
  > Add sharing definition for:
    * Job Grok Application

-------------------- 1.2.26 [30-mar-2010] ---------------------
- Add the declaration of JFile in jms2winfactory.php.
- Add computation of "ShareDB" based on the template value.
  This allow now creating website from the front-end with the "shareDB" functionality.
- Add the possibility to retreive the site information in the utility API.
  Maybe usefull for the plugin and other "external" component or modules

-------------------- 1.2.25 [19-mar-2010] ---------------------
- Remove the new "secret" value computed and introduced in the version 1.2.23.
  This has a side effect on the Single Sign-In that does not return the same session_id
  when the secret value are different.

-------------------- 1.2.24 [15-mar-2010] ---------------------
- When creating a slave site, it may happen that some view contain additional statement at the end like
  ..... from `dbname`.`tablename` WITH CASCADED CHECK OPTION'.
  To retreive the retreiving "FROM table name" present in a view statement we add a cross-check
  that it is preceded with the word "from".
- Bundled with Patch Definition 1.2.31.
  > Add JMS Tools (install) definitions for :
    * Add and fix DOCMan 1.5.4 modules and plugins, ARI Quiz, Vodes, Jobs
  > Add sharing definition for:
    * Vodes, Jobs

-------------------- 1.2.23 [06-mar-2010] ---------------------
- Set new "secret" code for each configuration when creating a website.
  This allow having specific cache file when the cache directory is shared between website
  but also allow having specific "JReviews" configuration that use the same "cache" method to save
  a copy of the configuration on disk.
- Add the possibility to create JMS templates or slave site based on an existing one.
  Add the "New Like" functionality.
- Add the "search" filtering in the "manage site" to allow retreive specific slave site.
  The filtering is applied to :
  * Site ID
  * Site name
  * Domains
  * DB host name
  * DB name
  * DB Prefix
- Remove the older compatibility with Joomla < 1.5.10 to reduce the package size.
  The original Joomla files < 1.5.10 are no more packaged in the distribution.
  Jms Multi Site is no more guaranted with older joomla < 1.5.10.
- Bundled with Patch Definition 1.2.30. + 1.2.29 (below)
  > Add JMS Tools (install) definitions for :
    - DOCMan 1.5.4 modules and plugins,
      Mutlisites Meta tag, CEdit, Click Head,
    - JReviews & S2Framework (not guaranteed - ioncube) - experimental - require new fresh slave site
      or slave site built with JMS 1.2.23 kernel (update is not working).
      Need to modify the secret word in configuration.php file.
- Bundled with Patch Definition 1.2.29.
  > Add fix for the YooThemes "yoo_vox" to allow reading the appropriate multisites "params_xxxx.ini" file.
  > Add JMS Tools (install) definitions for :
    - Frontpage SlideShow 2.x, Ninjaboard, Ozio Gallery 2, Picasa Slideshow, 
      Slimbox, Very Simple Image Gallery Plugin, 
      YOOaccordion, YOOcarousel, YOOdrawer, YOOeffects, YOOgallery, YOOholidays,
      YOOiecheck, YOOlogin, YOOmaps, YOOscroller, YOOsearch, YOOslider, YOOsnapshots,
      YOOtooltip, YOOtoppanel, YOOtweet
  > Add partial sharing definition for :
    - AcyMailing and only VM Users.
      This definition present risks for the consistency.
      It is required that the AcyMailing plugins that import content (except user details) should be de-activated.

-------------------- 1.2.22 [19-feb-2010] ---------------------
- Bundled with Patch Definition 1.2.28.
  > Add JMS Tools (install) definitions for :
    - Logos Query Manager (LQM)
  > Add partial sharing definition for :
    - HP Hot Property to allow only sharing the Agents and Companies.
  > Add sharing definition for:
    - Logos Query Manager (LQM)

-------------------- 1.2.21 [15-feb-2010] ---------------------
- Add parsing of the PORT in the URL to allow processing URL containing :80 or :443 inside the URL
- Resolve and save the new FTP parameter into the "slave" configuration file
- Bundled with Patch Definition 1.2.27.
  > Modify AcyMailing patch to ignore the patch concerning the license when using a free license.
  > Add JMS Tools (install) definitions for :
    * Community ACL, JCalPro, HD FLV Player, Modules Anywhere, pure css tooltip, Shipseeker
  > Add sharing definition for:
    * JCalPro, ALinkExchanger, HD FLV Player
    
-------------------- 1.2.20 [01-feb-2010] ---------------------
- Add possibility to "ignore" the images and templates folder in the JMS templates
  to allow creating rule where deploy folder is empty (No Symbolic Links at all).
- Add the possibility to select which field must be entered from the front-end.
  Now it is possible to hide the admin email, psw in case where creating website
  from the front-end that share the users. This avoid to reset the admin email and psw.
- Add possibility to define specific layout (templates) for the front-end website creation.
  This is associated to a new "layout" menu.
- Fix a bug when processing the creation of a slave site from the front-end.
  In this case, the multisites.cfg.php was not read and it was not possible to overwrite
  the "from" configuration parameters.
  So it was not possible to use a "MySQL" root login and also its host or ip address.
- Bundled with Patch Definition 1.2.26.
  > Add JMS Tools (install) definitions for :
    JComments several modules and plugins,
    JomSocial Dating Search & My Contacts,
    Jumi module,
    RawContent,
    sh404sef similar urls plugin

-------------------- 1.2.19 [19-jan-2010] ---------------------
- rebuilt with Patch definition 1.2.25 because some patches was not correctly included in the package.

-------------------- 1.2.18 [07-jan-2010] ---------------------
- Bundled with Patch Definition 1.2.24.
  > Fix sharing defintion for CK Forms 1.3.3 b5 (add new CK tables).
  > Fix Joomla Master configuration patch when upgrading from Patch 1.2.14 to 1.2.23
  > Add JMS Tools (install) definitions for :
    Content Optimzer, chabad, DT Menu, Fabrik - Tag Cloud module, Fabrik & facebook plugin,
    FLEXIcontent Tag Cloud,  Google Maps, J!Analytics, JCE Utilities, JComments Latest,
    System - JFusion plugin, JomFish Direct Translation, JoomlaPack Backup Notification Module,
    jSecure Authentication, Jumi plugins, JX Woopra, Mass content, System - OptimizeTables,
    RokGZipper, Session Meter, Zaragoza, Wibiya Toolbar, Button - Xmap Link 

-------------------- 1.2.17 [03-jan-2010] ---------------------
- Add error message when a configuration.php can not be read by JMS.
  Also accept to read "configuration.php" not terminated by a PHP end marker (?>)
  (Case of "configuration.php" file created by fantastico).
- Fix a javascript syntax error in the JMS tools (execute)
- Remove a PHP 5 warning message during the slave site joomla "install" action.
- Bundled with Patch Definition 1.2.23.
  > Add patch for JoomlaFCKEditor to allow the image manager used the slave site image folder
    and no more the master image folder.
  > Add a new master configuration.php patch algorithm in case where a double wrapper is installed.
  > Add JMS Tools (install) definitions for :
    * OpenX module, K2 modules.

-------------------- 1.2.16 [27-dec-2009] ---------------------
- Bundled with Patch Definition 1.2.22.
  > Add patch for AcyMailing multisites license.
  > Add JMS Tools (install) definitions for :
    * AcyMailing, FLEXIcontent, hwdVideoShare, jSeblod CCK, nBill (not tested)
  > Add sharing definition for:
    * AcyMailing
-------------------- 1.2.15 [19-dec-2009] ---------------------
- Fix a problem in the single sign-in when the domain contain second or third level domain
  (ie: co.uk or plc.co.im that must be recognized as a single element).
  JMS now include a Top Level Domain database to parse the URL for most of the countries
- Update HTML produced for the front-website creation to fix some side effects on some browser
  with DIVs that was not correctly closed.
- Fix PHP 4.x compatibility error introduced with the Joomla 1.6 alpha 2 compatibility.
- Bundled with Patch Definition 1.2.21.
  > Also modify a patch to allow patch the master configuration.php file when it is not finished
    by a PHP end marker (?>). Case that can happen with Fantastico that create the configuration.php
    without this marker.
  > Add a patch to RokModuleOrder to allow read the appropriate "params.ini" file 
    when used in a slave site
  > Add JMS Tools (install) definitions for :
    * IDoBlog

-------------------- 1.2.14 [24-nov-2009] ---------------------
- Add partial Joomla 1.6 alpha 2 compatibility.
- Add the possibility to modify the "jms templates" FTP parameters into the "manage site".
  The FTP parameters are simply written into the slave site configuration and not used
  to deploy the slave site.
- Bundled with Patch Definition 1.2.20.
  > Add JMS Tools (install) definitions for :
    * Editor Button - Add to Menu, AdminBar Docker,
      Advanced Modules, Cache Cleaner, BreezingForms,
      Ignite Gallery, JA Content Slider, JA Slideshow2,
      ProJoom Installer, RokBox, RokModuleOrder, RokModule,
      RSSeo, Smart Flash Header, Tag Meta, Zoo
  > Add sharing definition for:
    * Remository

-------------------- 1.2.13 [18-nov-2009] ---------------------
- Add the possibility to copy the images directory event when there is no DB specified.
  In this case it is not possible to change the "images" directory path but create an "images" directory
  base on a copy (or unzip) of the image.
- Add the possibility to perform a "RewriteBase" modification when copying the ".htaccess" or "htaccess.txt"
  to compute a new value when the target domain is not defined as a subdirectory of a domain or subdirectory of a subdomain.
  This maybe usefull when the website that is replicated use SEF extension enable that require an "htaccess".
- Add the possibility to define new FTP parameters for the "configuration.php" files create for the slave site.
  This maybe usefull when using the FTP Layer and that the FTP root path is different for each websites.
- Bundled with Patch Definition 1.2.19.
  > Add JMS Tools (install) definitions for :
    * Glossary, googleWeather, J!Research, Job Grok Listing,
      JooMap, JoomDOC, JXtended Catalog, JXtended Labels,
      Power Slide Pro, Rquotes,
      Add plenty Modules and plugin present JomSocial 1.5
      partial JomSuite membership, partial JomSuite user registration
  > Add sharing definition for:
    * Glossary, JXtended Catalog, JXtended Labels

-------------------- 1.2.12 [03-nov-2009] ---------------------
- Fix a problem in JMS Tools to allow display correct icon when some extension use
  definition with and without wildcard.
- Bundled with Patch Definition 1.2.18.
  > Add a patch definition for:
    * Hot Property modules and plugins
- Bundled with Joomla 1.5.15
- Bundled with Patch Definition 1.2.17.
  - Add a patch definition for FrontPage SlideShow.
  * FrontPage SlideShow, Lyften bloggie
- Bundled with Patch Definition 1.2.16.
  - Add a patch definition for eWeather.
  * camelcitycontent2, eWeather, Joomla Tags, Versions
- Bundled with Patch Definition 1.2.15.
  * JomRes, core DocMan modules

-------------------- 1.2.11 [22-oct-2009] ---------------------
- Give the possibility to also install a "core" module when it is defined in the "dbtable.xml".
  Roket Themes install most of the plugins and modules as "core" extensions.
- Bundled with Patch Definition 1.2.14.
  > Add a patch definition for ACE SEF.
  > Modify the VM patches to be compatible with VM 1.1.4
  > Add JMS Tools (install) definitions for :
    * AceSEF, AEC modules and plugins, ALFContact, AvReloaded,
      Core Design Login module, Chrono Comments, iJoomla Ad Agency,
      ImageSlideShow, Jobline, JoomlaFCK editor, 
      RokCandy, RokDownloads, RokNavMenu, RokNewsPager, RokQuickCart,
      RokSlideshow, RokStories, RokTabs, RokTwittie, Simple Mp3 Bar,
      All Weblinks, Library Management, Gavick PhotoSlide GK2
      
-------------------- 1.2.10 [14-oct-2009] ---------------------
- Fix the "template folder" copy when the directory to copy contain symbolic links.
  In this case, copy the content of each symbolic links into a physical directory.
- Bundled with Patch Definition 1.2.13.
  Add a patch definition for SermonSpeaker.
  Add JMS Tools (install) definitions for :
  * SermonSpeaker and PrayerCenter, News Pro GK1, Huru Helpdesk
  
-------------------- 1.2.9 [10-oct-2009] ---------------------
- Fix a bug when computing the cookie_domains.
  In fact the detection of the physical shared table name was incorrectly computed
  that has resulted to put two dbname in front of the table name.
  The consequence was a bad users table relationship detection and therefore JMS
  concluded that users are independents (and not shared).

- Bundled with Patch Definition 1.2.12.
  Add a patch definition for JRECache.
  Add JMS Tools (install) definitions for :
  * JRECache, DTRegister, JConnect, JIncludes,
    several modules and plugins for fabrik,
    SuperFishMenu, ALinkExchanger
  
-------------------- 1.2.8 [29-sep-2009] ---------------------
- Add the possibility to directly "add" a slave site from the front-end without a first access to the list of slave site.
  Also added the possibility to use the "redirect URL" when the process is completed.
- Remove a PHP warning in the UpdateSiteInfo routine in charge to update the status and the other information of a site.
  This routine may be called from plugin like Bridge for VirtueMart.

-------------------- 1.2.7 [25-sep-2009] ---------------------
- Improve the Symbolic Link detection in the case where the Global Configuration defines
  tmp or logs directory that does not exists. In this case, try to use the tmp and logs directory 
  that are probably present in the root of the website.
  The new implementation include a part of the "hello world" algorithm in case where the logs directory is not present.
- Implement an alternate algorithm to compute the "fromUserTableName" when MySQL SHOW create VIEW is not allowed.
  In this case, use the template ID to simulate the result of the SHOW CREATE VIEW.
- Add "is_writable" during the check of permission concerning the list or patches to install.
  The objective is to try reporting more "permission diagnosis" on potential reason of an patch installation failure.
- Avoid to backup the JMS manifest file that maybe restored with old values in case of patch "uninstall".
- Also binded with JMS patches definition v1.2.11
  that adds a patch definition for the single sign-in to allow restoring the session data 
  when some platform ignore them for sub-domain.
  This rescue procedure check that session data is correctly restored by the server when the Joomla session is shared.
  If the session is not restored by the server, this rescue procedure consists in rebuilding 
  the missing session data based on the infos stored by joomla in the session table.
  
  Add JMS Tools (install) definitions for :
  * Jom Comments, Simple Image Gallery Plugin, Phoca Maps, Phoca Restaurant Menu,
    Frontend User Access, CK Forms, JForms, RSForms!Pro, Plugin Multisite ID, Leads Capture.
  Add sharing definition for:
  * CK Forms
-------------------- 1.2.6 [20-sep-2009] ---------------------
- Remove the language translation using the "sitename" to avoid fatal error in language file line 171.
  When a "::" is present in a sitename, this crash the language JText:_() function that
  interpret the "::" as a class separator.
- Add a basic "cookie domain" computation to allow single sign-in on a subdomain.
- Fix display of the "sharing" tabs when creating a new JMS templates.
  It uses now the "master DB" setting to detect if the MySQL views are supported.
- Improve installation of fresh slave site when the hosting server does not follow correctly
  the symbolic links. Now create an installation directory in which symbolic links are created.
- Add possibility to directly copy the templates folders and also the possbility to create
  a templates folders based on unzip file.
- Also binded with JMS patches definition v1.2.9 and v1.2.10
  Add (install) definitions for :
  * FAQ2Win, Seminar for joomla!, ARTIO JoomSEF, SMF 2.x Bridge, 
    Billets, WordPress MU, JTAG Presentation for Slidshare,
    JCE MediaObject, JomComment, Mini Front End module,
    MyBlog, Remository Latest Entry module,
    Phoca Gallery, Poll XT, Vinaora Vistors Counter
  Add sharing definition for:
  * Seminar for joomla!, Billets, WordPress MU, JTAG Presentation for Slidshare

-------------------- 1.2.5 [6-sep-2009] ---------------------
- Fix JMS Tools install to also copy the data during the installation
  and not only the table structure.
- Add brasilian - help redirection.
- Also binded with JMS patches definition v1.2.8
  Add (install) definitions for :
  * Appointment Booking Pro v1.4x, Linkr, Chrono Forms, swMenuPro

-------------------- 1.2.4 [29-aug-2009] ---------------------
- Add brasilian - portuges language.
- Add partial Joomla 1.6 alpha compatibility to already allow the installation of JMS
  replicate websites and install extensions.
  The un-install of extension is not yet compatible.
- Fix minor bug in JMS tools display when component, modules or plugins are present in a slave site 
  after it is deleted in the master website
- Remove some PHP5 warning messages
- Bundled with Patch Definition 1.2.7 that introduce a patch compatibility with Joomla 1.6!
  and add the definition of several extension for the JMS Tools and sharing.
  Add (install) definitions for :
  * JoomGallery, RSFirewall, Phoca SEF
  * Fix a problem in the definition of sh404SEF
  Add sharing definition for:
  * JoomGallery, RSFirewall
  * Fix a problem in the sharing definition of kunena forum that was not recognized.

-------------------- 1.2.3 [14-aug-2009] ---------------------
- Fix a problem when replicating a website into another DB located in another server.
  JMS now check that the DB already exists before trying create a new DB located 
  on the same server than the DB to replicate
- Bundled with Patch Definition 1.2.6 that add the definition of several extension for the JMS Tools and sharing.
  Add sharing definition for:
  * QuickFAQ

-------------------- 1.2.2 [13-aug-2009] ---------------------
- Give the possibility to also install a "core" plugin that is defined in the "dbtable.xml".
  This new feature is helpful when some extension add "core" plugins to Joomla 
  and that must also be installed or propagate into websites using the JMS Tools.
- Bundled with Patch Definition 1.2.5 that add the definition of several extension for the JMS Tools and sharing.
  Add (install) definitions for :
  * AEC Subscription Manager, Joomla Knowledgebase, QuickFAQ, uddeIM, Xmap
  Add sharing definition for:
  * AEC Subscription Manager, Joomla Knowledgebase

-------------------- 1.2.1 [07-aug-2009] ---------------------
- Fix bug in the un-install of JMS that crash during the un-install.

-------------------- 1.2.0 [02-aug-2009] ---------------------
- Add display of the latest version number in the about and in the "check patches".
- Bundled with Patch Definition 1.2.4 that add the definition of several extension for the JMS Tools and sharing.
  Add (install) definitions for :
  * AlphaUserPoints, civiCRM, Content Templater, FrontpagePlus, JContentPlus,
    Mosets Tree, noixACL, ReReplacer
  Add sharing definition for:
  * AlphaUserPoints, Custom Properties, JContentPlus, K2, Kunena Forum, MisterEstate,
    Mosets Tree, Noix ACL
- Revamp all icons for the JMS Template / Sharing and JMS Tools
- Bundled with Joomla 1.5.14


-------------------- 1.2.0 RC5 [26-july-2009] ---------------------
- Disable the "check patches" installation button when JMS is administrate from a slave site.
  Normally, JMS should only be installed on the master website and should not be used from a slave site.
  The installation of the JMS patches from a slave site may have side effect on the slave site configuration.php
  that receive the JMS wrapper.
  This cause a PHP errors that result in the impossibility to access the slave site.
- Add new Tools extension install definitions for K2, WATicketSystem.
  Update also the Tools installation definition to add module and plugin defintion for the extension
  JomSocial, virtuemart, hwdVideoShare, JComments
  Fix a bug in the JEvents sharing definition
- Remove some PHP 5.x warnings.
- Add several "index.html" files into all the JMS directories to hide the directory structure.
  Mainly add an "index.html" files into the "/multisites" directory to hide the list of slave sites.
- Fix a problem when creating the DB dynamically.
  In that case, the tables was not replicated due to a bug in processing of the return code of the DB creation.
  In fact JMS has processed a sucessfull DB creation like an error that had resulted by a stop in creation of the tables.
- Fix mapping directory path displayed when using the deploy directory.
  Instead of displaying the "master" directory, now display the deploy directory.
  Also display the resolved domain name instead of the expression when this is possible.
- Fix bug when process slave site creation that report an error.
  On error, call the appropriate onDeploy_Err() plugin function instead of onDeploy_OK().
  This avoid for example to redirect the user to a check-out when its websiste quota is exceeded.
  

-------------------- 1.2.0 RC4 [10-july-2009] ---------------------
- Add verification on the Sharing definitions to avoid error in case of wrong XML files layout.
- Verify that the symbolic link information is really not available when using a relative path.
  Add the same processing with a full path computed based on current directory.
  It seems that PHP 5.2.8 or specific hosting provider may not return the symbolic link information
  when using a relative path. The verification consist in repeating the operation on a full path.
- Add a verification that the "template" is written.
  It may happen that permission on the directory does not autorize to write the "config_template.php" file.
  
-------------------- 1.2.0 RC3 [27-june-2009] ---------------------
- Add spanish translations.
- Fix several warnings concerning deprecated syntax in PHP 5.x.
- Bundled with original Joomla 1.5.12 files
- Add several extension definitiion and sharing extension
  Install definition:
  * AdsManager, Communicator, hwdCommentsFix, hwdPhotoShare, hwdRevenueManager, hwdVideoShare,
    JComments, Kunena Forum, NeoRecruit, Phoca Guestbook, 
  sharing definition:
  * AdsManager, NeoRecruit, hwdPhotoShare, hwdRevenueManager, hwdVideoShare.
  

-------------------- 1.2.0 Release Candidate 2 [26-june-2009] ---------------------
- Fix sanitization of the DB user name, DB password and also password generator.
  The new valid characters set is :
  * Letters : 'a' to 'z', 'A' to 'Z';
  * Digits  : '0' to '9';
  * Special characters: '_.,;:=-+*/@#$£!&(){}[]<>§'
- Add tables and sharing defintions for the extension
  EventList, JEvents, com-properties
- Fix bug when replicating a DB and syncrhonizing the Components, Modules and Plugins menus.
  In previous version, JMS duplicate the menu records into the DBs.
  
-------------------- 1.2.0 Release Candidate 1 [20-june-2009] ---------------------
- Add the replication of website into another DB
- Add a tools menu to supervise, install/share and uninstall extension in slave sites
- Add user sharing and limite extension sharing.
- Add possibility to create the deployment folder and an alias folder
- Add an expiration redirect URL

-------------------- 1.1.23 Stable Release [07-june-2009] ---------------------
- Packaged with Patch Definition 1.1.11 that contain the patch for hot property extension.

-------------------- 1.1.22 Stable Release [03-june-2009] ---------------------
- Just bundled with the Joomla 1.5.11 installation directory.
  Also contain some original Joomla 1.5.11 that may be used during the un-install of JMS when
  the original Joomla files was not backup or are no more present for any reason.
  (Used by JMS during a resuce un-install processing to ensure that original Joomla files are correctly restored).

-------------------- 1.1.21 Stable Release [21-apr-2009] ---------------------
- Add the possibility to use the {site_id} keyword into the domain definition of the "website template".
- Extend the execution time limit of 60 second in case where the upload took too much time.

-------------------- 1.1.20 Stable Release [20-apr-2009] ---------------------
- Add the possibility to use the {site_id} keyword into the domain definition in the "manage site".
- In the "manage" website, set the real URL into the domain name instead of the keywords to allow a direct
  access to the website.
- Reset the live_site field in the configuration.php in case where the "from" site configuration.php file
  contain a value. This avoid to redirect the slave site to a wrong URL (The value present in the live_site).
- Fix problem when saving the template and when there is an apostrophe (') present in the name of a folder
  or in a file name. In that case, the data save was corrupted (PHP syntax error) and it was impossible to read the template
  giving an empty list of website template.
- Include the Patch Definition 1.1.9 that contain fix for SH404SEF and AlphaContent.

-------------------- 1.1.19 Stable Release [03-apr-2009] ---------------------
- Fix a problem when installing extension having similar name like (email and mail).
  In this case when JMS search for the previous installation of the extension "mail", it can also
  find the "email" because the word "mail" is present in "email".
  As JMS take the first manifest file, it may select the wrong one and reply that you don't install
  the correct version of the extension.
  With this fix, new control are added when there are several manifest files having similar name.
  In this case, it checks if it find one manifest with exactly the expected name.

-------------------- 1.1.18 Stable Release [14-mar-2009] ---------------------
- remove the usage of 'floor' function that has in PHP 5.2.5 and cause problem during the registration of JMS
- In Community Build 1.2 RC2, the patch on plugin.foundation.php can be ignored.
- Fix a bug in JACLPlus that does not allow perform fresh Joomla Installation when installed in Joomla.
  The patch consist in a verification that the "configuration.php" file is present.
  Otherwise, the JACLPlus is disabled.
  
-------------------- 1.1.17 Stable Release [16-feb-2009] ---------------------
- Include the Patch Definition version 1.1.7 that allow using the slave deploy directory as administrator
  root directory. This allow access the specific image folder when specified.

-------------------- 1.1.16 Stable Release [13-feb-2009] ---------------------
- Ensure that MULTISITES_MASTER_ROOT_PATH has a correct value event when JMS is managed from a slave site.
  To guarantee the value, when JMS is called from a slave sites and a deploy directory is used,
  use the current MULTISITES_MASTER_ROOT_PATH value to write into the master JMS index.
- Include the Patch Definition version 1.1.6 with Community Builder slave site specific configuration files
- When using the creation of a website from the front-end and using the master website as "website template"
  template to replicate, it was not possible to retreive the login name from the DB because it was required
  to use a slave site for the replication.
  Now it is possible to use the master website as website to replicate from the front-end.

-------------------- 1.1.15 Stable Release [03-feb-2009] ---------------------
- Fix a problem on Database connection when using Symbolic Link.
  The problem was identified with "Articles Sharing" that does not connect on the Master DB when symbolic link
  was used and the slave site was deployed in a specific directory. In this case JMS was using the slave site
  configuration file instead of the master website directory because it has consider the current root directory
  as the master website that was wrong.

-------------------- 1.1.14 Stable Release [28-jan-2009] ---------------------
- Fix a session problem that does not allow login on the administration of the website on some secured environment
  using FTP Layer.

-------------------- 1.1.13 Stable Release [12-jan-2009] ---------------------
- Some UNIX platform resolve the __FILE__ variable differently depending if this the file itself or an included file.
  To solve the value of JPATH_BASE that is computed in the deployed index.php redirection file, 
  the redirection included is replaced by an evaluation of its content.
  This allow extension like JCE to compute correctly the path of group or this like that.
- Fix a date problem for system other than english (problem with french and date with accentuated characters).
  Also fix the expiration date dialog box displayed that have erratic date displayed when format was not appropriate.
- Fix one french error message.
- Display the username in addition the the name of the administrator that can be used from the front-end website created.
- Include the Patch Definition version 1.1.5
- Update some spanish messages
- Add a confirmation message box when un-installing the patches to give the opportunity to cancel the operation.

-------------------- 1.1.12 Stable Release [10-jan-2009] ---------------------
- Include the Joomla 1.5.9 security fixes.

-------------------- 1.1.11 Stable Release [06-jan-2009] ---------------------
- Fix problem of saving of the new email and password when creating a slave site from the front-end.
- Fix the login name displayed in the front-end when creating a slave site.
  The name that was displayed was get from the master website instead of the website defined in the template.
- Remove a warning on "SymLink" that may be displayed by some system.
  Warning: symlink() [function.symlink]: Permission denied in /home/public_html/administrator/components/com_multisites/helpers/helper.php on line 77

-------------------- 1.1.10 Stable Release [03-jan-2009] ---------------------
- Fix the display of validity unit in the template.
  Whe selecting the "month" or "year", the validity is correctly saved but when editing, the value presented
  was always "days".
  The fix now display the correct value.
- Include Patch Definition Version 1.1.4

-------------------- 1.1.9 Stable Release [29-december-2008] ---------------------
- Add spanish translation provided by Huitzi Torres from Mexico.
- Put some debug trace in comment to avoid produce warning when some variables are not defined.

-------------------- 1.1.8 Stable Release [26-december-2008] ---------------------
- Fix a problem in the communication layer used by the registration that may result in the creation of a 
  double host name in the REQUEST_URI. This error only occurs when the PHP curl module is not present.
- Fix problem in the URL parsing and in processing global $_SERVER when request comes from Windows client.
  Some Windows client seems to fill the REQUEST_URI with the full URL that make the JURI::getInstance return
  an invalid URL because it is processed like apache that does not put the host in front of the REQUEST_URI.
  Due to this duplicate host, the URL computed by JURI is wrong and contain something like http://xxxxhttp://xxxx
- Fix a problem when update of a "website template" when some field value are removed.
  When saving the "website template", the removed field was not removed.
  This version fix this problem.
- Fix a problem in the front-end delete website caused by a call to a missing function.
- Fix cosmetic problem concerning the display of the "delete" button in the front-end that disappear after
  processing an operation.
- Add some missing mezssage definition in the language files (back-end and front-end).
  
-------------------- 1.1.7 Stable Release [23-december-2008] ---------------------
- Fix problem in the implementation of the rescue registration.
- Security Fix to avoid that a slave site with Super Administrator rights manage
  the JMS websites when the extension is not installed.
  This avoid the forgery of the URL with option=com_multisites.
  
-------------------- 1.1.6 Stable Release [23-december-2008] ---------------------
- Review some Tips message to better explain the "deployed directory".
  Check that "deploy directory" exists to reduce the number of error message returned by JMS.
  Check that "deploy directory" is not your root directory.
- Review message on the registration and also implement a rescue registration when there is a permission problem.
  Reset JMS when "missing registration info" is returned during the registration in aim to retry the registration.

-------------------- 1.1.5 Stable Release [18-december-2008] ---------------------
- Refresh the JMS internal master index when a website is deleted to remove the domain URL path recognized by JMS.
  This will not only remove the files and folder on the disk as weel the tables int the DB 
  but also remove the URL that was recognized by JMS.
  Prior verion have to save any slave sites to force the master index updated.
- Fix problem when using specific "template folder".
  With prior version, the specific template folder was correctly replicated and used by the slave site
  but it was not possible to standard Joomla to install or management the template in the new directory.
  This new version fix the problem relative to the installer and the Patch Definition 1.1.3 solve the problem
  in the administration of the template.
  
-------------------- 1.1.4 Stable Release [09-december-2008] ---------------------
- Fix a problem when creating a slave site from the master DB and without using a template.
  In this case, the creation of the slave site was not possible.
- Fix a bug in when duplicating some slave Site due to mis interpreation of the "_" character in a prefix table/
  MySQL use "_" as a single wildcard character when it is not escaped.
  Now convert all "_" into "\_" to be interpreted as a character and not as a wildcard character.
- Fix a bug when working on Windows platform concerning the special copy of the image and media folder.
  The system try to create symbolic links that is a functionality that does not exists on Windows platform
  and generate an error message.

-------------------- 1.1.3 Stable Release [03-december-2008] ---------------------
- Replace getString by getCmd when reading the site ID to avoid special characters and the spaces.
  Some customer are using spaces in the name of a site id.
- Cleanup also the site_prefix and site_alias to replace getString by getCmd for the same reason
  than the "id".
- Fix also jms2winfactory to only use alphanumeric part of a site ID when computing the internal
  configuration class name. This avoid error when a site ID contain a dot (.) or a minus (-).
- Add more reporting message errors during the site deployment.
- Ignore error when updating a website and Symbolic Links are identical.
  This avoid to report error when a user update a website without updating the Symbolic Links
  During the creating of a Symbolic Link, if it already exists with the same path, ignore the error.
- Add checking when creating Symbolic Links to verify when it already exists, it they correspond to the same path.
  In this case, does not report an error when creating an Symbolic Links that already exists with the same parameters
  Also add some error message in case of DeploySite failure.
  Add a control that Image and Media folder exists during the "special copy".
  When a slave sites is created with a deployment directory and NO DB, in this case,
  the image and media folder are not copied because the "to db" parameters can not be written.
  In this case, the special copy will create a Symbolic Link on the master directory.

-------------------- 1.1.2 Stable Release [1-december-2008] ---------------------
- On some system, the creation of a slave site based on a template can fail due to a duplication
  of the "From DB" configuratoin in "To DB" configuration that may not work.
  On some system, the "From DB" config and "To DB" config are identical and the copy may not work.
  With fix, we have clone the "From DB" configuration to create the "To DB" configuration.
  With clone, the "From DB" and "To DB" configuration don't share the same memory.
  When the fix is not present, the symptom is an error 
  [Unable to retreive in the "From" Global Configuration the media folder path or it is empty]
- Fix the "Toolips Keyword" to show the {site_id} keyword and remove the duplicate {user_id}
- Fix the usage of {site_id} keyword;
- When editing a site, fix the template combo box refresh for Unix platform.
  A mispealled field ID cause the refresh failled.
- Include the "Patch Definition 1.1.2" with DOCMan configuration
- Fix bug in Delete Site when the table prefix is "_".
  MySQL use "_" as a single wildcard character when it is not escaped.
  Now convert all "_" into "\_" to be interpreted as a character and not as a wildcard character.
  The result is that when the "_" table prefix is used, this may delete all the tables of the database.
- Add Manage Site filtering on the Owner  

-------------------- 1.1.1 Stable Release [22-novembre-2008] ---------------------
- For Unix platform, add a checks if the Symbolic Links are available.
  On some very secured environement that require using FTP Layer, it is probable that Symbolic Links are forbidden.
  So add a check on Symbolic Link processing deployment 
  and also add a check on template folder replication using Symbolic Links that will be replaced by a copy
  when it is not available. If both fail, report an error to the user.
- Fix some cosmetic errors and some language entry missing.

-------------------- 1.1.0 Stable Release [18-november-2008] ---------------------
- Add possibility to use a complete URL in the definition of a slave site.
  For Unix platform:
  * It is possible to use sub-directories and a deploy the slave site into another directory than the master website.
  * It is possible to defined symbolic links or cut link to have a specific directory for a slave site.
- Possibility to define multiple slave site template in aim to replicate them for a new slave site
  When Unix, the content of the front-end template directory is duplicated by the creation of symbolic links.
- Possibity to replicate a Database prefix into another prefix (Require mySQL 4.1.x or higher).
  For the moment, only in the SAME database.
- Add Website template definition and add possibility to create slave website from the front-end
  based on website templates. Also contain a "Billable" front-end website payment facility.
- Remove the test on 'jos_' table prefix for Joomla >= 1.5.3.
  It seems it is solved or the bug was present in the "installation" directory and as we deliver the Joomla 1.5.7
  installation directory, the issue seems to be closed.

-------------------- 1.0.16 Stable Release [14-nov-2008] ---------------------
Fix installation version checking that return an error for Joom!fish 2.0 RC1 and
that identify the path folder as Joom!fish instead of Joomfish (without exclamation mark).

-------------------- 1.0.15 Stable Release [31-oct-2008] ---------------------
Include the patch definition version 1.0.12

-------------------- 1.0.14 Stable Release [26-oct-2008] ---------------------
Include the patch definition version 1.0.11

-------------------- 1.0.13 Stable Release [17-october-2008] ---------------------
Include the patch definition version 1.0.10

-------------------- 1.0.12 Stable Release [06-october-2008] ---------------------
Include the patch definition version 1.0.9

-------------------- 1.0.11 Stable Release [13-september-2008] ---------------------
Include the patch definition version 1.0.8

Fix a problem when an error occurs during the creation of a slave site.
The error occurs when the directory where must be create the slave site report a permissin deny.
In this case, the error message was incorrect could cause a redirection error.

Catchable fatal error: Object of class JException could not be converted 
to string in /....../libraries/joomla/application/application.php on line 302

-------------------- 1.0.10 Stable Release [12-september-2008] ---------------------
Packaging with Joomla 1.5.7.

-------------------- 1.0.9 Stable Release [11-september-2008] ---------------------
Fix problem minor return code when deletion of a site. 

Fix problem during the installation of a slave site when user request to "install data sample"
Add "jajax.php" patch into the installer to accept "install sample data".
The error was due by a detection of the master configuration file instead of slave configuration file.
The error message was:
Undefined index: DBtype in /....../joomla/installation/installer/models/model.php on line 764

Fix URL consistency for the HELP screen. Sometime, the www.jms2win.com was used and other time help.jms2win.com.
So now all the help screen are retreived from help.jms2win.com.

-------------------- 1.0.8 Stable Release [27-August-2008] ---------------------
Fix problem in the configuration wrapper to remove the inconsistencies in the definition between
save global configuration and the installation of the patches. 

Fix error message that could be reported during the uninstall of the extension and after having uninstall
the patches. This error appears when trying to remove a file that is already removed.

Fix a bug when CURL extension is not present in PHP.
When CURL is not present, JMS is designed to use an alternative communication layer 
for the registration, check for update, ..., all the communications with www.jms2win.com website.

-------------------- 1.0.7 Stable Release [24-August-2008] ---------------------
When Joomla is setup to used the FTP Layer, the mkdir, unlink, copy, ... can report warnings and error
when PHP is in Safe mode or base_dir is not defined.
Replace the native mkdir, unlink, copy, ... functions by the JFolder, JFile equivalent
in aim to reduce problem linked to the permission that use the Joomla FTP layer when it is enabled.

Fix bug during the "uninstall" that could report errors when some patches are installed.
To avoid this error with previous version, it is adviced to use the MultiSites / Check Patches / Uninstall
option before Joomla/uninstall the extension itself.

-------------------- 1.0.6 Stable Release [22-August-2008] ---------------------
Integration of Patch definition 1.0.5 that correct a bug in the configuration wrapper.
Due to an error in the configuration file name, a syntax error can appear due to an attempt
to redefine the configuration multiple times.
This error only appear with extension that use the configuration directly
and when it is called from a slave site.

Also change the 'jos_' error level into notice level.
Update the comment to explain in which condition 'jos_' table prefix is allowed.
'jos_' table prefix can only be used when this is the only website that is stored in the database.
(Case of as many databases than there are websites).

Also include the "backup_on_install" directory in the distribution to avoid using mkdir command
that could be disabled or have insuffisent privilege.

-------------------- 1.0.5 Stable Release [18-August-2008] ---------------------
Packaging with Joomla 1.5.6 and fix bug in the internal JMS product version retreival.

-------------------- 1.0.4 Stable Release [10-August-2008] ---------------------
Bug Fix:
Correct the path displayed when a slave site created. This is the path where the slave site must be mapped.
The path was wrong for all platform except WINDOWS.

Enhancement:
- Add a check to verify that 'multisites' directory is created at the installation.
  When not created, this is a symtom that JMS has not enought permission.
- Add some file permission checking to inform the user he has not enough rights to apply the patches.

-------------------- 1.0.3 Stable Release [04-August-2008] ---------------------
Enhancement:
- Add a check if the master table prefix is jos_
  If yes, report an ERROR to the user.
  Keep jos_ as table prefix will result in deletion of all the users and therefore result in the impossibility
  to login into the master website.

-------------------- 1.0.2 Stable Release [02-August-2008] ---------------------
Bug Fix:
In the version 1.0.0, the 'configuration.php' file in the master directory was not touched.
Some extension perform a direct access to this 'configuration.php' file that will result
in a wrong database connection and database table prefix.
To solve this problem, this version add a wrapper to the Master 'configuration.php' file
in aim to root it to the appropriate slave site. This means also that a patch is delivered
to allow saving the configuration with this wrapper.


Enhancement:
Add JMS2WinFactory and Jms2WinModel to allow connection to the Master database.
This new feature open the door to single User Authentication, Single content management and other
component that will be develop using the Master site as repository for the other slave sites.

-------------------- 1.0.1 Stable Release [01-August-2008] ---------------------
Bug Fix:
Problem when updating a slave site.
The update does not work because internally the site id was lost.

-------------------- 1.0.0 Stable Release [28-July-2008] ---------------------
First public distribution.
