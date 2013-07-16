/*
 * FCKeditor - The text editor for internet
 * Copyright (C) 2003-2006 Frederico Caldeira Knabben
 * 
 * Licensed under the terms of the GNU Lesser General Public License:
 * 		http://www.opensource.org/licenses/lgpl-license.php
 * 
 * For further information visit:
 * 		http://www.fckeditor.net/
 * 
 * "Support Open Source software. What about a donation today?"
 * 
 * File Name: fckplugin.js
 * 	Plugin to insert "FileBrowser_Thumbnail" in the editor.
 * 
 * File Authors:
 * 		Akira Taniguchi (akira@behappy.2-d.jp)
 */

FCKConfig.LinkBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;
FCKConfig.LinkBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ;

FCKConfig.ImageBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;
FCKConfig.ImageBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ;

FCKConfig.FlashBrowserWindowWidth  = FCKConfig.ScreenWidth * 0.7 ;
FCKConfig.FlashBrowserWindowHeight = FCKConfig.ScreenHeight * 0.7 ;

// ##### use php for magic3 modified by naoki #####
var url = FCKPlugins.Items[ 'FileBrowser_Thumbnail' ].Path + 'browser.html?Connector=connectors/php/connector.php' ;

if ( FCKConfig.ServerPath )
	url += '&ServerPath=' + FCKConfig.ServerPath ;

FCKConfig.LinkBrowserURL = url ;
FCKConfig.ImageBrowserURL = url + '&Type=Image' ;
FCKConfig.FlashBrowserURL = url + '&Type=Flash' ;
