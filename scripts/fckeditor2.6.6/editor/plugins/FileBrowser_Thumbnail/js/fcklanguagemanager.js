/*
 * FCKeditor - The text editor for Internet - http://www.fckeditor.net
 * Copyright (C) 2003-2008 Frederico Caldeira Knabben
 *
 * == BEGIN LICENSE ==
 *
 * Licensed under the terms of any of the following licenses at your
 * choice:
 *
 *  - GNU General Public License Version 2 or later (the "GPL")
 *    http://www.gnu.org/licenses/gpl.html
 *
 *  - GNU Lesser General Public License Version 2.1 or later (the "LGPL")
 *    http://www.gnu.org/licenses/lgpl.html
 *
 *  - Mozilla Public License Version 1.1 or later (the "MPL")
 *    http://www.mozilla.org/MPL/MPL-1.1.html
 *
 * == END LICENSE ==
 *
 * Defines the FCKLanguageManager object that is used for language
 * operations.
 *
 * Modified 2008.6.10 by Naoki Hirata (naoki@aplo.co.jp)
 * Direct Accessing to File Browser
 */

var FCKLanguageManager = 
{
	TranslatePage : function( targetDocument )
	{
		this.TranslateElements( targetDocument, 'INPUT', 'value' ) ;
		this.TranslateElements( targetDocument, 'SPAN', 'innerHTML' ) ;
		this.TranslateElements( targetDocument, 'LABEL', 'innerHTML' ) ;
		this.TranslateElements( targetDocument, 'OPTION', 'innerHTML', true ) ;
		this.TranslateElements( targetDocument, 'LEGEND', 'innerHTML' ) ;
	},
	
	TranslateElements : function( targetDocument, tag, propertyToSet, encode )
	{
		var e = targetDocument.getElementsByTagName(tag) ;
		var sKey, s ;
		for ( var i = 0 ; i < e.length ; i++ )
		{
			// The extra () is to avoid a warning with strict error checking. This is ok.
			if ( (sKey = e[i].getAttribute( 'fckLang' )) )
			{
				// The extra () is to avoid a warning with strict error checking. This is ok.
				if ( (s = FCKLang[ sKey ]) )
				{
					if ( encode )
						s = this.HTMLEncode( s ) ;
					e[i][ propertyToSet ] = s ;
				}
			}
		}
	},
	
	HTMLEncode : function( text )
	{
		if ( !text )
			return '' ;

		text = text.replace( /&/g, '&amp;' ) ;
		text = text.replace( /</g, '&lt;' ) ;
		text = text.replace( />/g, '&gt;' ) ;

		return text ;
	}
} ;
