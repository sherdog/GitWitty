<?php
/**
 * @file       patch_collection.php
 * @brief      Collection with all possible patches available.
 *
 * @version    1.2.53
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

defined('JPATH_MUTLISITES_COMPONENT') or die( 'Restricted access' );

$patchesVersion = '1.2.53';
/* History:
   - 1.0.1  14-JUL-2008 ECH: Add VirtueMart multiple configuration patch to accept multiple SECUREURL
   - 1.0.2  23-JUL-2008 ECH: Add Community Builder Plugin Installer patch to accept Overwrite.
   - 1.0.3  24-JUL-2008 ECH: Community Builder 1.1 stable & 1.2 RC2 patch compatibility.
   - 1.0.4  29-JUL-2008 ECH: Add wrapper in the master configuration.php file to route on the 
                            appropriate configuration.php file when an extension include the master file 
                            in direct from a slave site. (ie. Case of PayPal notify.php script in VirtueMart)
   - 1.0.5  21-AUG-2008 ECH: Fix problem in the wrapper for the file name 'configuration.php'. 
                            Remove '.cfg'. Related to version 1.0.4. 
   - 1.0.6  21-AUG-2008 ECH: Fix problem in the wrapper for the file name 'configuration.php'.
   - 1.0.7  11-SEP-2008 ECH: Add "jajax.php" patch into the installer to accept "install sample data".
   - 1.0.8  13-SEP-2008 ECH: Add a test to avoid duplicate definition of JConfig.
   - 1.0.9  06-OCT-2008 ECH: Add patch for the template in aim to have params.ini file specific for each websites.
   - 1.0.10 17-OCT-2008 ECH: Add patch for JCE Joomla Content Editor to force the overwrite of JCE installation when called by a slave site
   - 1.0.11 25-OCT-2008 ECH: Modify VirtueMart patch to have a complete independent slave configuration file.
                             Create a wrapper in the master VirtueMart.cfg.php file that allow to call slave if necessary.
   - 1.0.12 25-OCT-2008 ECH: Fix problem in VirtueMart patch V1.0.11 that redirect the slave to the master configuration
                             This new implementation is a mixt of original V1.0.1 and V1.0.11
   ------------------ Version 1.1.x ----------------
   - 1.1.0  29-SEP-2008 ECH: Change configuration.php wrapper to accept sub-directories.
            01-OCT-2008 ECH: Add multisites.php file for sub-directory matching
            24-OCT-2008 ECH: Add core Joomla Bug fix in JFolder::delete that destroy the content of a folder when it is a symbolic link
            30-OCT-2008 ECH: Add VirtueMart patch in ps_order to add a call to MultiSite plugin in aim to process VirtueMart onOrderStatusUpdate
            04-NOV-2008 ECH: Add VirtueMart patch in ps_checkout to all a call to MultiSite plugin in aim to process VirtueMart onAfterOrderCreate
   - 1.1.1  22-NOV-2008 ECH: Add patch in the installation to allow using the Joomla FTP Layer on a /multisites/xxx where xxx is the site ID
   - 1.1.2  28-NOV-2008 ECH: Add patch definition for DOCMan to allow specific configuration for each websites
   - 1.1.3  17-DEC-2008 ECH: Fix problem in standard joomla template management to allow using the specific "themes folder" path specified in JMS.
                             Fix problem in template installer to correctly use "themes folder" path specified in JMS.
   - 1.1.4  04-JAN-2009 ECH: Fix module position when the user has given a specific "themes folder" in JMS.
   - 1.1.5  12-JAN-2009 ECH: Add Article Sharing wrapper to avoid duplicate definition when it is present.
   - 1.1.6  10-FEB-2009 ECH: Add possibility to have specific Community Builder configuration file.
   - 1.1.7  14-FEB-2009 ECH: Add possibility to use the Slave site deploy directory instead of the master directory
                             to allow upload image, media, JCE upload, .... using the slave directory as root directory.
   - 1.1.8  13-MAR-2009 ECH: Fix problem in patch for CB 1.2 RC2
                             Remove the patch on CB plugin.foundation.php in case of 1.2 RC2
                             Add a patch for JACLPlus to disable the JACLPlus when there is no configuration.php files
                             (Case of the installation).
                             JACLPlus perform calls to the DB event when there is no configuration.php present and therefore
                             produce a Database Error during the standard Joomla Installation.
   - 1.1.9  17-APR-2009 ECH: Add a patch for SH404SEF to allow having specific configuration for each slave site.
                             There is a limitation. The security configuration remain common to all the websites.
                             This means that White list, Black list, User Agent White list and User Agent Black list remain shared (common).
                             The very advanced custom configuration remain also common to all the websites.
                             Add patch for AlphaContent in aim to have specific configuration file.
   - 1.1.10 20-APR-2009 ECH: Add a patch for JEvent to allow having specific configuration file for each website.
   - 1.1.11 07-JUN-2009 ECH: Add a patch for hotProperty to allow having specific configuration file for each website.
   ------------------ Version 1.2.x ----------------
   - 1.2.00 17-JUN-2009 ECH: Add the extension replication rules (DBTables.xml) and sharing extension replication rules (dbsharing.xml).
                             This current version contain the core Joomla, VirtueMart, Community Builder, JomSocial, Hot Property and SOBI 2
                             extensions definitions.
   - 1.2.01 26-JUN-2009 ECH: Add tables and sharing definitions for EventList, JEvents, com-properties.
   - 1.2.02 05-JUL-2009 ECH: Add tables (install) definitions for :
                             * AdsManager, Communicator, hwdCommentsFix, hwdPhotoShare, hwdRevenueManager, hwdVideoShare,
                               JComments, Kunena Forum, NeoRecruit, Phoca Guestbook, 
                             Add sharing definition for:
                             * AdsManager, NeoRecruit, hwdPhotoShare, hwdRevenueManager, hwdVideoShare.
   - 1.2.03 21-JUL-2009 ECH: Add tables (install) definitions for :
                             * K2, WATicketSystem, jsmallfib, jsmallist
                             Update JomSocial, virtuemart, hwdVideoShare, JComments to add module, plugin definitions
                             Fix JEvents sharing definition (some tables was not shared correctly).
   - 1.2.04 06-AUG-2009 ECH: Check the JMS version and patches version.
                             Add tables (install) definitions for :
                             * AlphaUserPoints, civiCRM, Content Templater, FrontpagePlus, JContentPlus,
                               Mosets Tree, noixACL, ReReplacer
                             Add sharing definition for:
                             * AlphaUserPoints, Custom Properties, JContentPlus, K2, Kunena Forum, MisterEstate,
                               Mosets Tree, Noix ACL
   - 1.2.05 12-AUG-2009 ECH: Add JMS Tools (install) definitions for :
                             * AEC Subscription Manager, Joomla Knowledgebase, QuickFAQ, uddeIM, Xmap
                             Add sharing definition for:
                             * AEC Subscription Manager, Joomla Knowledgebase
   - 1.2.06 14-AUG-2009 ECH: Add sharing definition for:
                             * QuickFAQ
   - 1.2.07 22-AUG-2009 ECH: Prepare compatibility with Joomla 1.6:
                             Remove XMLRPC patches as this functionality is removed in Joomla 1.6
                             Add other partial Joomla 1.6 specific patches
                             Add JMS Tools (install) definitions for :
                             * JoomGallery, RSFirewall, Phoca SEF
                             * Fix a problem in the definition of sh404SEF
                             Add sharing definition for:
                             * JoomGallery, RSFirewall
                             * Fix a problem in the sharing definition of kunena forum that was not recognized.
   - 1.2.08 06-SEP-2009 ECH: Add JMS Tools (install) definitions for :
                             * Appointment Booking Pro v1.4x, Linkr, Chrono Forms, swMenuPro
   - 1.2.09 08-SEP-2009 ECH: Add JMS Tools (install) definitions for :
                             * FAQ2Win, Seminar for joomla!, ARTIO JoomSEF, SMF 2.x Bridge
                             Add sharing definition for:
                             * Seminar for joomla!
   - 1.2.10 12-SEP-2009 ECH: Add JMS Tools (install) definitions for :
                             * Billets, WordPress MU, JTAG Presentation for Slidshare,
                               JCE MediaObject, JomComment, Mini Front End module,
                               MyBlog, Remository Latest Entry module,
                               Phoca Gallery, Poll XT, Vinaora Vistors Counter
                             Add sharing definition for:
                             * Billets, WordPress MU, JTAG Presentation for Slidshare
                             - Add patch definition to allow single sign-in for sub-domains
   - 1.2.11 24-SEP-2009 ECH: - Add patch definition for the single sign-in to allow restoring 
                               the session data when some platform ignore them for sub-domain.
                               This rescue procedure check that session data is correctly
                               restored by the server when the Joomla session is shared.
                               If the session is not restored by the server, this rescue procedure
                               consists in rebuilding the missing session data based on the infos
                               stored by joomla in the session table.
                             - Add JMS Tools (install) definitions for :
                             * Jom Comments, Simple Image Gallery Plugin,
                               Phoca Maps, Phoca Restaurant Menu, Frontend User Access,
                               CK Forms, JForms, RSForms!Pro, Plugin Multisite ID, Leads Capture
                             Add sharing definition for:
                             * CK Forms
   - 1.2.12 04-OCT-2009 ECH: - Add a patch definition for JRECache.
                             - Add JMS Tools (install) definitions for :
                             * JRECache, DTRegister, JConnect, JIncludes,
                               several modules and plugins for fabrik,
                               SuperFishMenu, ALinkExchanger
   - 1.2.13 13-OCT-2009 ECH: - Add a patch definition for SermonSpeaker.
                             - Add JMS Tools (install) definitions for :
                             * SermonSpeaker and PrayerCenter, News Pro GK1,
                               Huru Helpdesk
   - 1.2.14 17-OCT-2009 ECH: - Add a patch definition for ACE SEF.
                             - Modify the VM patches to be compatible with VM 1.1.4
                             - Add JMS Tools (install) definitions for :
                             * AceSEF, AEC modules and plugins, ALFContact, AvReloaded,
                               Core Design Login module, Chrono Comments, iJoomla Ad Agency,
                               ImageSlideShow, Jobline, JoomlaFCK editor, 
                               RokCandy, RokDownloads, RokNavMenu, RokNewsPager, RokQuickCart,
                               RokSlideshow, RokStories, RokTabs, RokTwittie, Simple Mp3 Bar,
                               All Weblinks, Library Management, Gavick PhotoSlide GK2
                               
   - 1.2.15 28-OCT-2009 ECH: Add JMS Tools (install) definitions for :
                             * JomRes, core DocMan modules
   - 1.2.16 01-NOV-2009 ECH: - Add a patch definition for eWeather.
                             - Add JMS Tools (install) definitions for :
                             * camelcitycontent2, eWeather, Joomla Tags, Versions
   - 1.2.17 03-NOV-2009 ECH: - Add a patch definition for FrontPage SlideShow.
                             - Add JMS Tools (install) definitions for :
                             * FrontPage SlideShow, Lyften bloggie
   - 1.2.18 10-NOV-2009 ECH: - Add JMS Tools (install) definitions for :
                             * Hot Property modules and plugins
   - 1.2.19 12-NOV-2009 ECH: Add JMS Tools (install) definitions for :
                             * Glossary, googleWeather, J!Research, Job Grok Listing,
                               JooMap, JoomDOC, JXtended Catalog, JXtended Labels,
                               Power Slide Pro, Rquotes
                               Add plenty Modules and plugin present JomSocial 1.5,
                               Add partial JomSuite.
                             Add sharing definition for:
                             * Glossary, JXtended Catalog, JXtended Labels
   - 1.2.20 08-DEC-2009 ECH: Add JMS Tools (install) definitions for :
                             * Editor Button - Add to Menu, AdminBar Docker,
                               Advanced Modules, Cache Cleaner, BreezingForms,
                               Ignite Gallery, JA Content Slider, JA Slideshow2,
                               ProJoom Installer, RokBox, RokModuleOrder, RokModule,
                               RSSeo, Smart Flash Header, Tag Meta, Zoo
                             Add sharing definition for:
                             * Remository
   - 1.2.21 18-DEC-2009 ECH: Add patch for rokmoduleorder plugin to read the appropriate
                             document "params.ini" file.
                             Also modify the patch for the master configuration to also
                             apply the patch when the PHP end marker is not present (?>)
                             This may happen with Fantastico that create the master configuration
                             without this marker.
                             Add JMS Tools (install) definitions for :
                             * IDoBlog
   - 1.2.22 21-DEC-2009 ECH: Add patch for AcyMailing multisites license
                             Add JMS Tools (install) definitions for :
                             * AcyMailing, FLEXIcontent, hwdVideoShare, jSeblod CCK,
                               nBill
                             Add sharing definition for:
                             * AcyMailing
   - 1.2.23 03-JAN-2010 ECH: Add patch for JoomlaFCKEditor to allow the image manager used the slave site image folder
                             and no more the master image folder.
                             Improve the Joomla master "configuration.php" patch to try avoid installing a double wrapper.
                             Add JMS Tools (install) definitions for :
                             * OpenX module
   - 1.2.24 16-JAN-2010 ECH: Fix CK Forms sharing definition to add new tables present in "1.3.3 b5"
                             Fix Joomla Master configuration patch when upgrading from Patch 1.2.14 to 1.2.23
                             Add JMS Tools (install) definitions for :
                             * Content Optimzer, chabad, DT Menu, Fabrik - Tag Cloud module, Fabrik & facebook plugin,
                             FLEXIcontent Tag Cloud,  Google Maps, J!Analytics, JCE Utilities, JComments Latest,
                             System - JFusion plugin, JomFish Direct Translation, JoomlaPack Backup Notification Module,
                             jSecure Authentication, Jumi plugins, JX Woopra, Mass content, System - OptimizeTables,
                             RokGZipper, Session Meter, Zaragoza, Wibiya Toolbar, Button - Xmap Link 
   - 1.2.25 19-JAN-2010 ECH: Rebuilt because some patch defined in patch 1.2.24 was not correctly included in the package.
   - 1.2.26 20-JAN-2010 ECH: Add patch for CCBoard to allow specific configuration file.
                             Add patch for sh404SEF to allow specific shCacheContent.php cache file for each slave sites.
                             Add JMS Tools (install) definitions for :
                             * JComments several modules and plugins,
                             JomSocial Dating Search & My Contacts,
                             Jumi module,
                             RawContent,
                             sh404sef similar urls plugin
   - 1.2.27 10-FEB-2010 ECH: Modify AcyMailing patch to ignore the patch concerning the license when using a free license.
                             Add JMS Tools (install) definitions for :
                             * Community ACL, JCalPro, HD FLV Player, Modules Anywhere, pure css tooltip, Shipseeker
                             Add sharing definition for:
                             * JCalPro, ALinkExchanger, HD FLV Player
   - 1.2.28 17-FEB-2010 ECH: Add JMS Tools (install) definitions for :
                             * Logos Query Manager (LQM)
                             Add partial sharing definition for :
                             * HP Hot Property to allow only sharing the Agents and Companies.
                             Add sharing definition for:
                             * Logos Query Manager (LQM)
   - 1.2.29 17-FEB-2010 ECH: Add fix for the YooThemes "yoo_vox" to allow reading the appropriate multisites "params_xxxx.ini" file.
                             Add JMS Tools (install) definitions for :
                             * Frontpage SlideShow 2.x, Ninjaboard, Ozio Gallery 2, Picasa Slideshow, 
                               Slimbox, Very Simple Image Gallery Plugin, 
                               YOOaccordion, YOOcarousel, YOOdrawer, YOOeffects, YOOgallery, YOOholidays,
                               YOOiecheck, YOOlogin, YOOmaps, YOOscroller, YOOsearch, YOOslider, YOOsnapshots,
                               YOOtooltip, YOOtoppanel, YOOtweet
                             Add partial sharing definition for :
                             * AcyMailing and only VM Users.
                               This definition present risks for the consistency.
                               It is required that the AcyMailing plugins that import content (except user details) should be de-activated.
   - 1.2.30 09-MAR-2010 ECH: Add JMS Tools (install) definitions for :
                             * DOCMan 1.5.4 modules and plugins
                               Mutlisites Meta tag, CEdit, Click Head,
                               JReviews & S2Framework (not guaranteed) - experimental.
   - 1.2.31 10-MAR-2010 ECH: Add JMS Tools (install) definitions for :
                             * Add and fix DOCMan 1.5.4 modules and plugins,
                               ARI Quiz, Vodes, Jobs
                             Add sharing definition for:
                             * Vodes, Jobs
   - 1.2.32 24-MAR-2010 ECH: Add JMS Tools (install) definitions for :
                             * Joomla Quiz, JUMultithumb, wbAdvert
                             Add sharing definition for:
                             * Joomla Quiz, Shipseeker
   - 1.2.33 24-APR-2010 ECH: Add PHP 5.3 compatibility and remove deprecated warning messages.
                             Add JMS Tools (install) definitions for :
                             * Job Grok Application, Kuneri Mobile Joomla, ninjaXplorer
                               Quick Jump Extended, WEBO Site SpeedUp,
                               aiContactSafe, Rentalot
                             Add sharing definition for:
                             * Job Grok Application
   - 1.2.34 29-APR-2010 ECH: Bundled with some Joomla 1.5.17 to prevent that older Jms Multisites installation
                             have problems when they do not yet updated their Jms Multisites kernel.
   - 1.2.35 03-MAY-2010 ECH: Modify 'admin index' patch to take in account the new letter tree directory structure
                             Add JMS Tools (install) definitions for :
                             * QContact, Multisites Affiliate, GroupJive, JoomLeague, myApi, OSE Webmail
                               Seo Links, Update Manager, VM Emails Manager, WebmapPlus
                             Add sharing definition for:
                             * GroupJive
   - 1.2.36 01-JUN-2010 ECH: Modify several existing patches to take in account the new letter tree directory structure
                             Add JMS Tools (install) definitions for :
                             * JS Testimonials
   - 1.2.37 06-JUN-2010 ECH: Fix a patch that corrupt the joomla template manager.
   - 1.2.38 09-JUN-2010 ECH: Add Joomla 1.6.0 beta 2 compatibility.
                             Add JMS Tools (install) definitions for :
                             * Joomla Flash Uploader, synk, Akeeba Backup
                               Blue Flame Forms For Joomla, Extended Menu, JB FAQ, JB Slideshow
                               JEV Location, Spec Images, JVideo!, JoomLoc
                             Add sharing definition for:
                             * JoomLoc
                             
                             Incompatibility with RocketThemes RokDownloadBundle version 1.0.1
                             The new RokDownload component is packaged into a RokDownloadBundle 
                             that install the RokDownload component as a core joomla component.
                             JMS Multisites does not provide interface to manage "core joomla component"
                             that normally are only the one provided by Joomla packaging.
   - 1.2.39 13-JUL-2010 ECH: Add patch for CssJsCompress.
                             Fix the patch for CB 1.2.3 that moved some code into another source
                             Add JMS Tools (install) definitions for :
                             * Annonces, CssJsCompress, Djf Acl, EstateAgent, jSecure Authentication, MooFAQ
                               QuickContent, PU Arcade, Ajax Chat
                               Add also some plugin for JomSocial 1.8.3
                             Add sharing definition for:
                             * Annonces, JVideo, PU Arcade, Ajax Chat
                               JEvent with default excluded.
   - 1.2.40 23-JUL-2010 ECH: Remove the Database Joomla 1.6 patch that is replaced by MultisitesDatabase instances
                             that avoid using patch to modify protected fields.
                             Add JMS Tools (install) definitions for :
                             * Ninja Content, Simple Caddy, Sourcerer, redEVENT, redFORM
                               Multisites Search plugins = MultisitesContent, MultisitesCategories, MultisitesSections;
                             Add sharing definition for:
                             * Simple Caddy, redEVENT, redFORM
   - 1.2.41 03-AUG-2010 ECH: Modify the SH404SEF patch to be complient with version 2.0.
                             Some patches where moved into other files after they have refactor their code to use MVC.
                             Add JMS Tools (install) definitions for :
                             * Multisites patches for Mighty,
                               Disqus Comment System for Joomla!,
                               Scribe, SEF Title Prefix and Suffix, some sh404SEF plugins,
                               Simply Links, WysiwygPro3
   - 1.2.42 10-AUG-2010 ECH: Fix the patch concerning the Global Configuration in Joomla 1.6.
                             Add JMS Tools (install) definitions for :
                             * Multisites Content Modules (NewsFlash and LatestNews),
                               Multisites Contact,
                               BreezingForms >= 1.7.2 (formelly Facile Forms),
                               Listbingo,
                               Projectfork,
                               RSTickets! Pro,
                               Community Builder Profile Pro + Magic Window,
                               Grid
                             Add sharing definition for:
                             * Ignite Gallery,
                               Community Builder Profile Pro + Magic Window
   - 1.2.43 11-SEP-2010 ECH: Fix AceSEF patch to be compatible with AceSEF version 1.5.x
   - 1.2.44 23-SEP-2010 ECH: Add JMS Tools (install) definitions for :
                             * AceSEF plugin,
                               Categories module, Joomdle, JoomFish SEF, Kunena 1.6, Scheduler
                             Add Joomla 1.5 sharing definition for:
                             * Kunena 1.6, Scheduler
                             Add Joomla 1.6 sharing definition for:
                             * Kunena 1.6
   - 1.2.45 05-OCT-2010 ECH: Add patch for Mobile Joomla
                             Add JMS Tools (install) definitions for :
                             * CB Search plugin, ai Sobi Search
   - 1.2.46 13-OCT-2010 ECH: Add JMS Tools (install) definitions for :
                             * Auctions, Restaurant Guide, CodeCitation plugin,
                               Versioning Workflow
                             Add Joomla 1.5 sharing definition for:
                             * Auctions,
                               Restaurant Guide 
                               with limitation on the Linked Articles that can not be used as the articles are not shared
   - 1.2.47 25-OCT-2010 ECH: Add JMS Tools (install) definitions for :
                             * Tiendra
                             Add Joomla 1.5 sharing definition for:
                             * Tiendra, Joomla Estate Agency
   - 1.2.48 05-NOV-2010 ECH: Fix a patch for compatibility with Joomla 1.5.22
                             and take in account the new fixes in the fork of sessions.
                             Add JMS Tools (install) definitions for :
                             * Copyright Current Year, jDownloads, JW Tabs & Slides Module
   - 1.2.49 12-NOV-2010 ECH: Add a patch for the All Video download script to compute the "document root" directory
                             based on the deployed directory.
                             The sitePath "document root" directory is not correct when using Symbolic Link.
                             Add JMS Tools (install) definitions for :
                             * All Video, Noku Framework, Ninja 1.5, Koowa
   - 1.2.50 06-DEC-2010 ECH: Add JMS Tools (install) definitions for :
                             * FLEXIaccess, HotelGuide, jShareEasy,
                               JV-LinkDirectory, JV-LinkExchanger
   - 1.2.51 15-DEC-2010 ECH: Remove most of the patches in Joomla 1.6 RC as they are no more required
                             and that adding new files can do the same without patches.
                             Add JMS Tools (install) definitions for :
                             * RS Events, iJoomla SEO
                             Add Joomla 1.5 sharing definition for:
                             * RS Events, RS Form
   - 1.2.52 13-JAN-2011 ECH: - Add patch for CBE (Community Builder Enhanced)
                             - Modify the patches for Joomla 1.6.0 stable
                             - Modify the patches for acesef 1.5.13 compatibility
                             Add JMS Tools (install) definitions for :
                             * CBE, GCalendar, JoomGallery Treeview, 
                               JSPT / XIPT / JomSocial Profile Types,
                               VM Affiliate Tracking Module,
                               WordPress
                             Add Joomla 1.5 sharing definition for:
                             * CBE, JSPT, WordPress
   - 1.2.53 01-FEB-2011 ECH: - Add patch in Joomla 1.6 to make it compatible with Joomla 1.5
                             Add JMS Tools (install) definitions for :
                             * AutoTweet, Attend JEvents,
                               HikaShop, Newsletter, JoomailerMailchimpSignup
                               Joomlart extensions manager, JA Buletin, JA MegaMenu,
                               JA News2 Module, JA News Ticker Module,
                               JA News Frontpage Module, JA Tabs
                               JA Twitter, JA Bookmark
                               JA Disqus Debate Echo Plugin, JA Thumbnail
                               JA Popup, JA Section menu plugin
                               JA T3 Framework, JA User Setting
                               JoomShopping, Nurte Facebook Like Button
                               OSE UPMan
                               RSComments!, RSMail, RSMembership!
                               WDBanners
                             Fix Joomla 1.5 sharing definition for:
                             * Tienda
                             Add Joomla 1.5 sharing definition for:
                             * HikaShop, 
                             * Affiliate Text Ads
*/


// If Joomla 1.6
if ( version_compare( JVERSION, '1.6') >= 0) { 
   $version = new JVersion();
   $vers_status = strtoupper( $version->DEV_STATUS);
   // If not a beta (mean RC or stable)
   $pos = strpos( $vers_status, 'beta');
   if ($pos === false) {
      // RC or Stable
      $files2patch = array( 'administrator/components/com_multisites/extension.xml'   => 'JMSVers',
                            'administrator/defines.php'          => 'defines',
                            'defines.php'                        => 'defines',
                            'includes/defines_multisites.php'    => 'ifPresent',
                            'includes/multisites.php'            => 'ifPresent',
                            'installation'                       => 'ifDirPresent',
                            'installation/index.php'             => 'defines',
                            'configuration.php'                  => 'masterConfig',
                            'administrator/components/com_config/models/application.php'     => 'JConfig16',
                            '/libraries/joomla/installer/installer.php'                      => 'legacy15GetInstance'
                          );
   }
   // Joomla 1.6 beta
   else {
      $files2patch = array( 'administrator/components/com_multisites/extension.xml'   => 'JMSVers',
                            'administrator/defines.php'          => 'defines',
                            'administrator/index.php'            => 'AdminIndex',
                            'administrator/includes/defines.php' => 'defines',
                            'defines.php'                        => 'defines',
                            'includes/defines.php'               => 'defines',
                            'includes/defines_multisites.php'    => 'ifPresent',
                            'includes/multisites.php'            => 'ifPresent',
                            'installation'                       => 'ifDirPresent',
                            'installation/index.php'             => 'defines',
                            'configuration.php'                  => 'masterConfig',
                            'administrator/components/com_config/models/application.php'           => 'JConfig16'
                          );
   }
}
// Else: Default Joomla 1.5
else {
   $files2patch = array( 'administrator/components/com_multisites/install.xml'   => 'JMSVers',
                         'administrator/index.php'            => 'AdminIndex',
                         'administrator/includes/defines.php' => 'defines',
                         'includes/defines.php'               => 'defines',
                         'includes/defines_multisites.php'    => 'ifPresent',
                         'includes/multisites.php'            => 'ifPresent',
                         'installation'                       => 'ifDirPresent',
                         'installation/includes/defines.php'  => 'InstallDefines',
                         'installation/installer/helper.php'  => 'InstallHelper',
                         'installation/installer/jajax.php'   => 'defines',
                         'xmlrpc/includes/defines.php'        => 'defines',
                         'configuration.php'                  => 'masterConfig',
                         'administrator/components/com_config/controllers/application.php'      => 'JConfig',
                         'administrator/components/com_installer/models/templates.php'          => 'tpl_basedir',
                         'administrator/components/com_modules/models/module.php'               => 'module_tpl',
                         'administrator/components/com_templates/admin.templates.html.php'      => 'params_ini_tpl',
                         'administrator/components/com_templates/controller.php'                => 'params_ini_cntl',
                         'libraries/joomla/application/application.php'                         => 'LibApplication',
                         'libraries/joomla/document/html/html.php'                              => 'params_ini_html',
                         'libraries/joomla/filesystem/folder.php'                               => 'JFolder',
                         'libraries/joomla/session/session.php'                                 => 'LibSession',
                         'libraries/joomla/user/user.php'                                       => 'LibUser',
                         'components/com_content/helpers/route.php'                             => 'ContentHelperRoute',
                         'plugins/system/remember.php'                                          => 'PlgRemember',
                         // Extensions
                         'administrator/components/com_acesef/configuration.php'                => 'ACESEFCfgWrapper',
                         'administrator/components/com_acesef/models/acesef.php'                => 'ACESEFSaveAceCfg',
                         'administrator/components/com_acesef/models/config.php'                => 'ACESEFSaveCfg',
                         'administrator/components/com_acymailing/helpers/update.php'           => 'AcyMailingSaveLi',
                         'administrator/components/com_alphacontent/configuration/configuration.php' => 'AlphaContentWrapper',
                         'administrator/components/com_alphacontent/models/alphacontent.php'         => 'AlphaContentSaveCfg',
                         'administrator/components/com_cbe/admin.cbe.php'                       => 'CBESaveCfg',
                         'administrator/components/com_cbe/ue_config.php'                       => 'CBECfgWrapperUE',
                         'administrator/components/com_cbe/enhanced_admin/enhanced_config.php'  => 'CBECfgWrapperEnhanced',
                         'administrator/components/com_ccboard/ccboard-config.php'              => 'CCBoardCfgWrapper',
                         'administrator/components/com_ccboard/models/general.php'              => 'CCBoardSaveCfg',
                         'administrator/components/com_comprofiler/admin.comprofiler.controller.php'  => 'CB_cntl',
                         'administrator/components/com_comprofiler/controller/controller.default.php' => 'CB_cntl',
                         'administrator/components/com_comprofiler/plugin.foundation.php'       => 'CB_plg_foundation',
                         'administrator/components/com_comprofiler/library/cb/cb.installer.php' => 'CBInstaller',
                         'administrator/components/com_docman/docman.class.php'                 => 'DOCManClass',
                         'administrator/components/com_events/admin.events.html.php'            => 'JEventShowConfig',
                         'administrator/components/com_events/lib/config.php'                   => 'JEventSaveCfg',
                         'administrator/components/com_eweather/eweather.config.php'            => 'eWeatherConfig',
                         'administrator/components/com_fpslideshow/configuration.php'           => 'FPSSCfgWrapper',
                         'administrator/components/com_fpslideshow/admin.fpslideshow.php'       => 'FPSSSaveCfg',
                         'administrator/components/com_hotproperty/includes/defines.php'        => 'HPConfig',
                         'administrator/components/com_jce/installer/installer.php'             => 'JCE',
                         'administrator/components/com_jrecache/jrecache.config.php'            => 'JRECfgWrapper',
                         'administrator/components/com_jrecache/controls/configuration.php'     => 'JRECtrlCfg',
                         'index.php'                                                            => 'JREIndex',
                         'administrator/components/com_jrecache/install_files/index.pat'        => 'JREIndex',
                         'administrator/components/com_jrecache/library/config.php'             => 'JRELibCfg',
                         'administrator/components/com_mobilejoomla/config.php'                 => 'MobJoomCfgWrapper',
                         'administrator/components/com_mobilejoomla/admin.mobilejoomla.php'     => 'MobJoomSaveCfg',
                         'administrator/components/com_sermonspeaker/sermoncastconfig.sermonspeaker.php' => 'SermonCastCfgWrapper',
                         'administrator/components/com_sermonspeaker/config.sermonspeaker.php'           => 'SermonCfgWrapper',
                         'administrator/components/com_sermonspeaker/controller.php'                     => 'SermonController',
                         'administrator/components/com_sh404sef/admin.sh404sef.php'             => 'SH404Admin',
                         'administrator/components/com_sh404sef/sh404sef.class.php'             => 'SH404Class',
                         'administrator/components/com_sh404sef/SEFConfig.class.php'            => 'SH404Class',
                         'administrator/components/com_sh404sef/config/config.sef.php'          => 'SH404SefWrapper',
                         'administrator/components/com_sh404sef/models/urls.php'                => 'SH404URLS',
                         'components/com_sh404sef/shCache.php'                                  => 'SH404CacheContent',
                         'administrator/components/com_virtuemart/classes/ps_checkout.php'      => 'VMPlgAfterOrder',
                         'administrator/components/com_virtuemart/classes/ps_config.php'        => 'VMConfig',
                         'administrator/components/com_virtuemart/classes/ps_order.php'         => 'VMPlgUpdStatus',
                         'administrator/components/com_virtuemart/virtuemart.cfg.php'           => 'VMCfgWrapper',
                         'plugins/content/jw_allvideos/includes/download.php'                   => 'AllVideoDownload',
                         'plugins/editors/fckeditor/editor/plugins/ImageManager/config.inc.php' => 'FCKEdCfgInc',
                         'plugins/system/CssJsCompress/css.php'                                 => 'CssJsCompressCSS',
                         'plugins/system/CssJsCompress/js.php'                                  => 'CssJsCompressJS',
                         'plugins/system/rokmoduleorder/document.php'                           => 'ROKModOrDoc',
                         'templates/yoo_vox/lib/php/template.php'                               => 'YOOVoxTemplate'
                       );
}


$corefiles2backup = array( 'defines', 'masterConfig', 'JConfig');

// Core JMS
include( dirname(__FILE__).DS.'joomla/check_jms_vers.php');

// Core Joomla files
include( dirname(__FILE__).DS.'joomla/check_admin_index.php');
include( dirname(__FILE__).DS.'joomla/check_content_hlp_route.php');
include( dirname(__FILE__).DS.'joomla/check_defines.php');
include( dirname(__FILE__).DS.'joomla/check_ifdirpresent.php');
include( dirname(__FILE__).DS.'joomla/check_ifpresent.php');
include( dirname(__FILE__).DS.'joomla/check_instdefines.php');
include( dirname(__FILE__).DS.'joomla/check_insthelper.php');
include( dirname(__FILE__).DS.'joomla/check_jconfig.php');
include( dirname(__FILE__).DS.'joomla/check_jconfig16.php');
include( dirname(__FILE__).DS.'joomla/check_jdatabase.php');
include( dirname(__FILE__).DS.'joomla/check_jfolder.php');
include( dirname(__FILE__).DS.'joomla/check_legacy15getinstance.php');
include( dirname(__FILE__).DS.'joomla/check_libapplication.php');
include( dirname(__FILE__).DS.'joomla/check_libsession.php');
include( dirname(__FILE__).DS.'joomla/check_libuser.php');
include( dirname(__FILE__).DS.'joomla/check_masterconfig.php');
include( dirname(__FILE__).DS.'joomla/check_module_tpl.php');
include( dirname(__FILE__).DS.'joomla/check_params_ini_tpl.php');
include( dirname(__FILE__).DS.'joomla/check_params_ini_cntl.php');
include( dirname(__FILE__).DS.'joomla/check_params_ini_html.php');
include( dirname(__FILE__).DS.'joomla/check_plgremember.php');
include( dirname(__FILE__).DS.'joomla/check_tpl_basedir.php');
// Extensions
include( dirname(__FILE__).DS.'acesef/check_config.php');
include( dirname(__FILE__).DS.'acesef/check_saveacecfg.php');
include( dirname(__FILE__).DS.'acesef/check_savecfg.php');
include( dirname(__FILE__).DS.'acymailing/check_saveli.php');
include( dirname(__FILE__).DS.'alphacontent/check_config.php');
include( dirname(__FILE__).DS.'alphacontent/check_savecfg.php');
include( dirname(__FILE__).DS.'cbe/check_savecfg.php');
include( dirname(__FILE__).DS.'cbe/check_ueconfig.php');
include( dirname(__FILE__).DS.'cbe/check_enhconfig.php');
include( dirname(__FILE__).DS.'ccboard/check_config.php');
include( dirname(__FILE__).DS.'ccboard/check_savecfg.php');
include( dirname(__FILE__).DS.'comprofiler/check_cb_cntl.php');
include( dirname(__FILE__).DS.'comprofiler/check_cb_plg_foundation.php');
include( dirname(__FILE__).DS.'comprofiler/check_cbinstaller.php');
include( dirname(__FILE__).DS.'CssJsCompress/check_css.php');
include( dirname(__FILE__).DS.'CssJsCompress/check_js.php');
include( dirname(__FILE__).DS.'events/check_savecfg.php');
include( dirname(__FILE__).DS.'events/check_showconfig.php');
include( dirname(__FILE__).DS.'eweather/check_config.php');
include( dirname(__FILE__).DS.'fckeditor/check_config.inc.php');
include( dirname(__FILE__).DS.'fpslideshow/check_config.php');
include( dirname(__FILE__).DS.'fpslideshow/check_savecfg.php');
include( dirname(__FILE__).DS.'hotproperty/check_config.php');
include( dirname(__FILE__).DS.'jce/check_jce.php');
include( dirname(__FILE__).DS.'jrecache/check_config.php');
include( dirname(__FILE__).DS.'jrecache/check_ctrlconfig.php');
include( dirname(__FILE__).DS.'jrecache/check_index.php');
include( dirname(__FILE__).DS.'jrecache/check_libconfig.php');
include( dirname(__FILE__).DS.'jw_allvideos/check_download.php');
include( dirname(__FILE__).DS.'docman/check_docmanclass.php');
include( dirname(__FILE__).DS.'mobilejoomla/check_config.php');
include( dirname(__FILE__).DS.'mobilejoomla/check_savecfg.php');
include( dirname(__FILE__).DS.'sermonspeaker/check_castconfig.php');
include( dirname(__FILE__).DS.'sermonspeaker/check_config.php');
include( dirname(__FILE__).DS.'sermonspeaker/check_controller.php');
include( dirname(__FILE__).DS.'sh404sef/check_admin.php');
include( dirname(__FILE__).DS.'sh404sef/check_cachecontent.php');
include( dirname(__FILE__).DS.'sh404sef/check_class.php');
include( dirname(__FILE__).DS.'sh404sef/check_config_sef.php');
include( dirname(__FILE__).DS.'sh404sef/check_urls.php');
include( dirname(__FILE__).DS.'virtuemart/check_config.php');
include( dirname(__FILE__).DS.'virtuemart/check_ps_checkout.php');
include( dirname(__FILE__).DS.'virtuemart/check_ps_order.php');
include( dirname(__FILE__).DS.'virtuemart/check_virtuemart_cfg.php');
include( dirname(__FILE__).DS.'rokmoduleorder/check_rokmodordoc.php');
include( dirname(__FILE__).DS.'yoo_vox/check_yoovoxtemplate.php');
