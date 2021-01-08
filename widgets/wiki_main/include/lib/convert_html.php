<?php
/**
 * HTML変換ライブラリ
 *
 * PHP versions 5
 *
 * LICENSE: This source file is licensed under the terms of the GNU General Public License.
 *
 * @package    Magic3 Framework
 * @author     平田直毅(Naoki Hirata) <naoki@aplo.co.jp>
 * @copyright  Copyright 2006-2015 Magic3 Project.
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version    SVN: $Id: convert_html.php 4951 2012-06-09 09:55:41Z fishbone $
 * @link       http://www.magic3.org
 */
// Copyright (C)
//   2002-2005 PukiWiki Developers Team
//   2001-2002 Originally written by yu-ji
// License: GPL v2 or (at your option) any later version
//
// function 'convert_html()', wiki text parser
// and related classes-and-functions

function convert_html($lines)
{
	global $digest;
	static $contents_id = 0;

	// comment for Magic3 by naoki on 2008/9/30
	// get_sourceが2度実行されている
	// Set digest
	$digest = md5(get_source(WikiParam::getPage(), true));
	WikiParam::setDigest($digest);		// ダイジェストの設定はここで行う

	if (! is_array($lines)) $lines = explode("\n", $lines);
//global $gErrorManager;
//$gErrorManager->writeDebug(__METHOD__, 'convert_html start');		// 時間計測用
	$body = new Body(++$contents_id);
	$body->parse($lines);
//$gErrorManager->writeDebug(__METHOD__, 'convert_html end');		// 時間計測用
	return $body->toString();
}

// Block elements
class Element
{
	var $parent;
	var $elements; // References of childs
	var $last;     // Insert new one at the back of the $last

//	function Element()
	function __construct()
	{
		$this->elements = array();
		$this->last     = $this;
	}

	function setParent($parent)
	{
		$this->parent = $parent;
	}

	function add($obj)
	{
		if ($this->canContain($obj)) {
			return $this->insert($obj);
		} else {
			return $this->parent->add($obj);
		}
	}

	function insert($obj)
	{
		$obj->setParent($this);
		$this->elements[] = $obj;

		return $this->last = $obj->last;
	}

	function canContain($obj)
	{
		return TRUE;
	}

	function wrap($string, $tag, $param = '', $canomit = TRUE)
	{
		return ($canomit && $string == '') ? '' : '<' . $tag . $param . '>' . $string . '</' . $tag . '>';
	}

	function toString()
	{
		$ret = array();
		foreach (array_keys($this->elements) as $key)
			$ret[] = $this->elements[$key]->toString();
		return join("\n", $ret);
	}

	function dump($indent = 0)
	{
		$ret = str_repeat(' ', $indent) . get_class($this) . "\n";
		$indent += 2;
		foreach (array_keys($this->elements) as $key) {
			$ret .= is_object($this->elements[$key]) ?
				$this->elements[$key]->dump($indent) : '';
				//str_repeat(' ', $indent) . $this->elements[$key];
		}
		return $ret;
	}
}

// Returns inline-related object
function Factory_Inline($text)
{
	// Check the first letter of the line
	if (substr($text, 0, 1) == '~') {
		return new Paragraph(' ' . substr($text, 1));
	} else {
		return new Inline($text);
	}
}

function Factory_DList($root, $text)
{
	$out = explode('|', ltrim($text), 2);
	if (count($out) < 2) {
		return Factory_Inline($text);
	} else {
		return new DList($out);
	}
}

// '|'-separated table
function Factory_Table($root, $text)
{
	if (! preg_match('/^\|(.+)\|([hHfFcC]?)$/', $text, $out)) {
		return Factory_Inline($text);
	} else {
		return new Table($out);
	}
}

// Comma-separated table
function Factory_YTable($root, $text)
{
	if ($text == ',') {
		return Factory_Inline($text);
	} else {
		return new YTable(csv_explode(',', substr($text, 1)));
	}
}

function Factory_Div($root, $text)
{
	$matches = array();

	// Seems block plugin?
	if (PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK) {
		// Usual code
		if (preg_match('/^\#([^\(]+)(?:\((.*)\))?/', $text, $matches) &&
		    exist_plugin_convert($matches[1])) {
			return new Div($matches);
		}
	} else {
		// Hack code
		if(preg_match('/^#([^\(\{]+)(?:\(([^\r]*)\))?(\{*)/', $text, $matches) &&
		   exist_plugin_convert($matches[1])) {
			$len  = strlen($matches[3]);
			$body = array();
			if ($len == 0) {
				return new Div($matches); // Seems legacy block plugin
			} else if (preg_match('/\{{' . $len . '}\s*\r(.*)\r\}{' . $len . '}/', $text, $body)) { 
				$matches[2] .= "\r" . $body[1] . "\r";
				return new Div($matches); // Seems multiline-enabled block plugin
			}
		}
	}

	return new Paragraph($text);
}

// Inline elements
class Inline extends Element
{
//	function Inline($text)
	function __construct($text)
	{
//		parent::Element();
		parent::__construct();
		$this->elements[] = trim((substr($text, 0, 1) == "\n") ?
			$text : make_link($text));
	}

	function insert($obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function canContain($obj)
	{
		return is_a($obj, 'Inline');
	}

	function toString()
	{
		global $line_break;
		return join(($line_break ? '<br />' . "\n" : "\n"), $this->elements);
	}

	function toPara($class = '')
	{
		$obj = new Paragraph('', $class);
		$obj->insert($this);
		return $obj;
	}
}

// Paragraph: blank-line-separated sentences
class Paragraph extends Element
{
	var $param;

//	function Paragraph($text, $param = '')
	function __construct($text, $param = '')
	{
		//parent::Element();
		parent::__construct();
		$this->param = $param;
		if ($text == '') return;

		if (substr($text, 0, 1) == '~')
			$text = ' ' . substr($text, 1);

		$this->insert(Factory_Inline($text));
	}

	function canContain($obj)
	{
		return is_a($obj, 'Inline');
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'p', $this->param);
	}
}

// * Heading1
// ** Heading2
// *** Heading3
class Heading extends Element
{
	var $level;
	var $id;
	var $msg_top;

//	function Heading($root, $text)
	function __construct($root, $text)
	{
		//parent::Element();
		parent::__construct();

		$this->level = min(3, strspn($text, '*'));
		list($text, $this->msg_top, $this->id) = $root->getAnchor($text, $this->level);
		$this->insert(Factory_Inline($text));
		$this->level++; // h2,h3,h4
	}

	function insert($obj)
	{
		parent::insert($obj);
		return $this->last = $this;
	}

	function canContain($obj)
	{
		return FALSE;
	}

	function toString()
	{
		global $gEnvManager;
		static $baseHTagLevel;		// コンテンツ内のヘッダタグレベル
		
		// 実行中のウィジェットを取得
		if (!isset($baseHTagLevel)){
			$widgetObj = $gEnvManager->getCurrentWidgetObj();
			if (!empty($widgetObj)) $baseHTagLevel = $widgetObj->getHTagLevel();

			// コンテンツにタイトルを表示している場合はタグレベルを更新
			if (WikiConfig::isShowPageTitle()) $baseHTagLevel++;
		}

//		return $this->msg_top .  $this->wrap(parent::toString(), 'h' . $this->level, ' id="' . $this->id . '"');
		return $this->msg_top .  $this->wrap(parent::toString(), 'h' . ($baseHTagLevel + $this->level -2), ' id="' . $this->id . '"');
	}
}

// ----
// Horizontal Rule
class HRule extends Element
{
//	function HRule($root, $text)
	function __construct($root, $text)
	{
		//parent::Element();
		parent::__construct();
	}

	function canContain($obj)
	{
		return FALSE;
	}

	function toString()
	{
		global $hr;
		return $hr;
	}
}

// Lists (UL, OL, DL)
class ListContainer extends Element
{
	var $tag;
	var $tag2;
	var $level;
	var $style;
	var $margin;
	var $left_margin;

//	function ListContainer($tag, $tag2, $head, $text)
	function __construct($tag, $tag2, $head, $text)
	{
		//parent::Element();
		parent::__construct();

		$var_margin      = '_' . $tag . '_margin';
		$var_left_margin = '_' . $tag . '_left_margin';
		global $$var_margin, $$var_left_margin;

		$this->margin      = $$var_margin;
		$this->left_margin = $$var_left_margin;

		$this->tag   = $tag;
		$this->tag2  = $tag2;
		$this->level = min(3, strspn($text, $head));
		$text = ltrim(substr($text, $this->level));

		parent::insert(new ListElement($this->level, $tag2));
		if ($text != '')
			$this->last = $this->last->insert(Factory_Inline($text));
	}

	function canContain($obj)
	{
		return (! is_a($obj, 'ListContainer')
			|| ($this->tag == $obj->tag && $this->level == $obj->level));
	}

	function setParent($parent)
	{
		global $_list_pad_str;

		parent::setParent($parent);

		$step = $this->level;
		if (isset($parent->parent) && is_a($parent->parent, 'ListContainer'))
			$step -= $parent->parent->level;

		$margin = $this->margin * $step;
		if ($step == $this->level)
			$margin += $this->left_margin;

		$this->style = sprintf($_list_pad_str, $this->level, $margin, $margin);
	}

	function insert($obj)
	{
		if (! is_a($obj, get_class($this)))
			return $this->last = $this->last->insert($obj);

		// Break if no elements found (BugTrack/524)
		if (count($obj->elements) == 1 && empty($obj->elements[0]->elements))
			return $this->last->parent; // up to ListElement

		// Move elements
		foreach(array_keys($obj->elements) as $key)
			parent::insert($obj->elements[$key]);

		return $this->last;
	}

	function toString()
	{
		return $this->wrap(parent::toString(), $this->tag, $this->style);
	}
}

class ListElement extends Element
{
//	function ListElement($level, $head)
	function __construct($level, $head)
	{
		//parent::Element();
		parent::__construct();
		$this->level = $level;
		$this->head  = $head;
	}

	function canContain($obj)
	{
		return (! is_a($obj, 'ListContainer') || ($obj->level > $this->level));
	}

	function toString()
	{
		return $this->wrap(parent::toString(), $this->head);
	}
}

// - One
// - Two
// - Three
class UList extends ListContainer
{
//	function UList($root, $text)
	function __construct($root, $text)
	{
		//parent::ListContainer('ul', 'li', '-', $text);
		parent::__construct('ul', 'li', '-', $text);
	}
}

// + One
// + Two
// + Three
class OList extends ListContainer
{
//	function OList($root, $text)
	function __construct($root, $text)
	{
		//parent::ListContainer('ol', 'li', '+', $text);
		parent::__construct('ol', 'li', '+', $text);
	}
}

// : definition1 | description1
// : definition2 | description2
// : definition3 | description3
class DList extends ListContainer
{
//	function DList($out)
	function __construct($out)
	{
		//parent::ListContainer('dl', 'dt', ':', $out[0]);
		parent::__construct('dl', 'dt', ':', $out[0]);
		$this->last = Element::insert(new ListElement($this->level, 'dd'));
		if ($out[1] != '')
			$this->last = $this->last->insert(Factory_Inline($out[1]));
	}
}

// > Someting cited
// > like E-mail text
class BQuote extends Element
{
	var $level;

//	function BQuote($root, $text)
	function __construct($root, $text)
	{
		//parent::Element();
		parent::__construct();

		$head = substr($text, 0, 1);
		$this->level = min(3, strspn($text, $head));
		$text = ltrim(substr($text, $this->level));

		if ($head == '<') { // Blockquote close
			$level       = $this->level;
			$this->level = 0;
			$this->last  = $this->end($root, $level);
			if ($text != '')
				$this->last = $this->last->insert(Factory_Inline($text));
		} else {
			$this->insert(Factory_Inline($text));
		}
	}

	function canContain($obj)
	{
		return (! is_a($obj, get_class($this)) || $obj->level >= $this->level);
	}

	function insert($obj)
	{
		// BugTrack/521, BugTrack/545
		if (is_a($obj, 'inline'))
			return parent::insert($obj->toPara(' class="quotation"'));

		if (is_a($obj, 'BQuote') && $obj->level == $this->level && count($obj->elements)) {
			$obj = $obj->elements[0];
			if (is_a($this->last, 'Paragraph') && count($obj->elements))
				$obj = $obj->elements[0];
		}
		return parent::insert($obj);
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'blockquote');
	}

	function end($root, $level)
	{
		$parent = $root->last;

		while (is_object($parent)) {
			if (is_a($parent, 'BQuote') && $parent->level == $level)
				return $parent->parent;
			$parent = $parent->parent;
		}
		return $this;
	}
}

class TableCell extends Element
{
	var $tag = 'td'; // {td|th}
	var $colspan = 1;
	var $rowspan = 1;
	var $style; // is array('width'=>, 'align'=>...);

//	function TableCell($text, $is_template = FALSE)
	function __construct($text, $is_template = FALSE)
	{
		//parent::Element();
		parent::__construct();
		$this->style = $matches = array();

		while (preg_match('/^(?:(LEFT|CENTER|RIGHT)|(BG)?COLOR\(([#\w]+)\)|SIZE\((\d+)\)):(.*)$/',
		    $text, $matches)) {
			if ($matches[1]) {
				$this->style['align'] = 'text-align:' . strtolower($matches[1]) . ';';
				$text = $matches[5];
			} else if ($matches[3]) {
				$name = $matches[2] ? 'background-color' : 'color';
				$this->style[$name] = $name . ':' . htmlspecialchars($matches[3]) . ';';
				$text = $matches[5];
			} else if ($matches[4]) {
				$this->style['size'] = 'font-size:' . htmlspecialchars($matches[4]) . 'px;';
				$text = $matches[5];
			}
		}
		if ($is_template && is_numeric($text))
			$this->style['width'] = 'width:' . $text . 'px;';

		if ($text == '>') {
			$this->colspan = 0;
		} else if ($text == '~') {
			$this->rowspan = 0;
		} else if (substr($text, 0, 1) == '~') {
			$this->tag = 'th';
			$text      = substr($text, 1);
		}

		if ($text != '' && $text[0] == '#') {
			// Try using Div class for this $text
			$obj = Factory_Div($this, $text);
			if (is_a($obj, 'Paragraph'))
				$obj = $obj->elements[0];
		} else {
			$obj = Factory_Inline($text);
		}

		$this->insert($obj);
	}

	function setStyle($style)
	{
		foreach ($style as $key=>$value)
			if (! isset($this->style[$key]))
				$this->style[$key] = $value;
	}

	function toString()
	{
		if ($this->rowspan == 0 || $this->colspan == 0) return '';

		$param = ' class="style_' . $this->tag . '"';
		if ($this->rowspan > 1)
			$param .= ' rowspan="' . $this->rowspan . '"';
		if ($this->colspan > 1) {
			$param .= ' colspan="' . $this->colspan . '"';
			unset($this->style['width']);
		}
		if (! empty($this->style))
			$param .= ' style="' . join(' ', $this->style) . '"';

		return $this->wrap(parent::toString(), $this->tag, $param, FALSE);
	}
}

// | title1 | title2 | title3 |
// | cell1  | cell2  | cell3  |
// | cell4  | cell5  | cell6  |
class Table extends Element
{
	var $type;
	var $types;
	var $col; // number of column
	public $align;		// テーブル表示位置
	
//	function Table($out)
	function __construct($out)
	{
		//parent::Element();
		parent::__construct();

		$cells       = explode('|', $out[1]);
		$this->col   = count($cells);
		$this->type  = strtolower($out[2]);
		$this->types = array($this->type);
		$is_template = ($this->type == 'c');
		$row = array();
		foreach ($cells as $cell)
			$row[] = new TableCell($cell, $is_template);
		$this->elements[] = $row;
	}

	function canContain($obj)
	{
		return is_a($obj, 'Table') && ($obj->col == $this->col);
	}

	function insert($obj)
	{
		$this->elements[] = $obj->elements[0];
		$this->types[]    = $obj->type;
		return $this;
	}

	function toString()
	{
		global $gEnvManager;
		
		static $parts = array('h'=>'thead', 'f'=>'tfoot', ''=>'tbody');

		// Set rowspan (from bottom, to top)
		for ($ncol = 0; $ncol < $this->col; $ncol++) {
			$rowspan = 1;
			foreach (array_reverse(array_keys($this->elements)) as $nrow) {
				$row = $this->elements[$nrow];
				if ($row[$ncol]->rowspan == 0) {
					++$rowspan;
					continue;
				}
				$row[$ncol]->rowspan = $rowspan;
				// Inherits row type
				while (--$rowspan)
					$this->types[$nrow + $rowspan] = $this->types[$nrow];
				$rowspan = 1;
			}
		}

		// Set colspan and style
		$stylerow = NULL;
		foreach (array_keys($this->elements) as $nrow) {
			$row = $this->elements[$nrow];
			if ($this->types[$nrow] == 'c')
				$stylerow = $row;
			$colspan = 1;
			foreach (array_keys($row) as $ncol) {
				if ($row[$ncol]->colspan == 0) {
					++$colspan;
					continue;
				}
				$row[$ncol]->colspan = $colspan;
				if ($stylerow !== NULL) {
					$row[$ncol]->setStyle($stylerow[$ncol]->style);
					// Inherits column style
					while (--$colspan)
						$row[$ncol - $colspan]->setStyle($stylerow[$ncol]->style);
				}
				$colspan = 1;
			}
		}

		// toString
		$string = '';
		foreach ($parts as $type => $part)
		{
			$part_string = '';
			foreach (array_keys($this->elements) as $nrow) {
				if ($this->types[$nrow] != $type)
					continue;
				$row        = $this->elements[$nrow];
				$row_string = '';
				foreach (array_keys($row) as $ncol)
					$row_string .= $row[$ncol]->toString();
				$part_string .= $this->wrap($row_string, 'tr');
			}
			$string .= $this->wrap($part_string, $part);
		}
		
		// 表示位置クラス
		switch ($this->align){
		case 'left':
		default:
			$addClass = '';
			break;
		case 'center':
			$addClass = ' center';
			break;
		case 'right':
			$addClass = ' right';
			break;
		}
		
		// テンプレートタイプに合わせて出力を変更
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$addClass .= ' table table-bordered';
		}
			
		$string = $this->wrap($string, 'table', ' class="style_table' . $addClass . '"');
		$string = $this->wrap($string, 'div', ' class="table_wrap"');
		return $string;
	}
}

// , title1 , title2 , title3
// , cell1  , cell2  , cell3
// , cell4  , cell5  , cell6
class YTable extends Element
{
	var $col;
	public $align;		// テーブル表示位置

//	function YTable($_value)
	function __construct($_value)
	{
		//parent::Element();
		parent::__construct();

		$align = $value = $matches = array();
		foreach($_value as $val) {
			if (preg_match('/^(\s+)?(.+?)(\s+)?$/', $val, $matches)) {
				$align[] =($matches[1] != '') ?
					((isset($matches[3]) && $matches[3] != '') ?
						' style="text-align:center"' :
						' style="text-align:right"'
					) : '';
				$value[] = $matches[2];
			} else {
				$align[] = '';
				$value[] = $val;
			}
		}
		$this->col = count($value);
		$colspan = array();
		foreach ($value as $val)
			$colspan[] = ($val == '==') ? 0 : 1;
		$str = '';
		$count = count($value);
		for ($i = 0; $i < $count; $i++) {
			if ($colspan[$i]) {
				while ($i + $colspan[$i] < $count && $value[$i + $colspan[$i]] == '==')
					$colspan[$i]++;
				$colspan[$i] = ($colspan[$i] > 1) ? ' colspan="' . $colspan[$i] . '"' : '';
				$str .= '<td class="style_td"' . $align[$i] . $colspan[$i] . '>' . make_link($value[$i]) . '</td>';
			}
		}
		$this->elements[] = $str;
	}

	function canContain($obj)
	{
		return is_a($obj, 'YTable') && ($obj->col == $this->col);
	}

	function insert($obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function toString()
	{
		global $gEnvManager;
		
		$rows = '';
		foreach ($this->elements as $str){
			$rows .= "\n" . '<tr class="style_tr">' . $str . '</tr>' . "\n";
		}
//		$rows = $this->wrap($rows, 'table', ' class="style_table" cellspacing="1" border="0"');
		//return $this->wrap($rows, 'div', ' class="ie5"');
		
		// 表示位置クラス
		switch ($this->align){
		case 'left':
		default:
			$addClass = '';
			break;
		case 'center':
			$addClass = ' center';
			break;
		case 'right':
			$addClass = ' right';
			break;
		}
		
		// テンプレートタイプに合わせて出力を変更
		$templateType = $gEnvManager->getCurrentTemplateType();
		if ($templateType == M3_TEMPLATE_BOOTSTRAP_30){		// Bootstrap型テンプレートの場合
			$addClass .= ' table table-bordered';
		}
		
		$rows = $this->wrap($rows, 'table', ' class="style_table' . $addClass . '"');
		$rows = $this->wrap($rows, 'div', ' class="table_wrap"');
		return $rows;
	}
}

// ' 'Space-beginning sentence
// ' 'Space-beginning sentence
// ' 'Space-beginning sentence
class Pre extends Element
{
//	function Pre($root, $text)
	function __construct($root, $text)
	{
		global $preformat_ltrim;
		//parent::Element();
		parent::__construct();
		$this->elements[] = htmlspecialchars(
			(! $preformat_ltrim || $text == '' || $text[0] != ' ') ? $text : substr($text, 1));
	}

	function canContain($obj)
	{
		return is_a($obj, 'Pre');
	}

	function insert($obj)
	{
		$this->elements[] = $obj->elements[0];
		return $this;
	}

	function toString()
	{
		//return $this->wrap(join("\n", $this->elements), 'pre');
		return $this->wrap(join("\n", $this->elements), 'pre', ' class="wiki_pre"');		// modified for magic3
	}
}

// Block plugin: #something (started with '#')
class Div extends Element
{
	var $name;
	var $param;

//	function Div($out)
	function __construct($out)
	{
		//parent::Element();
		parent::__construct();
		list(, $this->name, $this->param) = array_pad($out, 3, '');
	}

	function canContain($obj)
	{
		return FALSE;
	}

	function toString()
	{
		// Call #plugin
		return do_plugin_convert($this->name, $this->param);
	}
}

// LEFT:/CENTER:/RIGHT:
class Align extends Element
{
	var $align;

//	function Align($align)
	function __construct($align)
	{
		//parent::Element();
		parent::__construct();
		$this->align = $align;
	}

/*	function canContain($obj)
	{
		return is_a($obj, 'Inline');
	}*/
	function canContain($obj)
	{
		// テーブルの表示位置
		if (is_a($obj,'Table') or is_a($obj,'YTable'))
		{
			$obj->align = $this->align;
		}
		return is_a($obj, 'Inline');
	}

	function toString()
	{
		return $this->wrap(parent::toString(), 'div', ' style="text-align:' . $this->align . '"');
	}
}

// Body
class Body extends Element
{
	var $id;
	var $count = 0;
	var $contents;
	var $contents_last;
	var $classes = array(
		'-' => 'UList',
		'+' => 'OList',
		'>' => 'BQuote',
		'<' => 'BQuote');
	var $factories = array(
		':' => 'DList',
		'|' => 'Table',
		',' => 'YTable',
		'#' => 'Div');

//	function Body($id)
	function __construct($id)
	{
		$this->id            = $id;
		$this->contents      = new Element();
		$this->contents_last = $this->contents;
//		parent::Element();
		parent::__construct();
	}

	function parse($lines)
	{
		$this->last = $this;
		$matches = array();

		while (! empty($lines)) {
			$line = array_shift($lines);

			// Escape comments
			if (substr($line, 0, 2) == '//') continue;

			if (preg_match('/^(LEFT|CENTER|RIGHT):(.*)$/', $line, $matches)) {
				// <div style="text-align:...">
				$this->last = $this->last->add(new Align(strtolower($matches[1])));
				if ($matches[2] == '') continue;
				$line = $matches[2];
			}

			$line = rtrim($line, "\r\n");

			// Empty
			if ($line == '') {
				$this->last = $this;
				continue;
			}

			// Horizontal Rule
			if (substr($line, 0, 4) == '----') {
				$this->insert(new HRule($this, $line));
				continue;
			}

			// Multiline-enabled block plugin
			if (! PKWKEXP_DISABLE_MULTILINE_PLUGIN_HACK &&
			    preg_match('/^#[^{]+(\{\{+)\s*$/', $line, $matches)) {
				$len = strlen($matches[1]);
				$line .= "\r"; // Delimiter
				while (! empty($lines)) {
					$next_line = preg_replace("/[\r\n]*$/", '', array_shift($lines));
					if (preg_match('/\}{' . $len . '}/', $next_line)) {
						$line .= $next_line;
						break;
					} else {
						$line .= $next_line .= "\r"; // Delimiter
					}
				}
			}

			// The first character
			$head = $line[0];

			// Heading
			if ($head == '*') {
				$this->insert(new Heading($this, $line));
				continue;
			}

			// Pre
			if ($head == ' ' || $head == "\t") {
				$this->last = $this->last->add(new Pre($this, $line));
				continue;
			}

			// Line Break
			if (substr($line, -1) == '~')
				$line = substr($line, 0, -1) . "\r";
			
			// Other Character
			if (isset($this->classes[$head])) {
				$classname  = $this->classes[$head];
				$this->last = $this->last->add(new $classname($this, $line));
				continue;
			}

			// Other Character
			if (isset($this->factories[$head])) {
				$factoryname = 'Factory_' . $this->factories[$head];
				$this->last  = $this->last->add($factoryname($this, $line));
				continue;
			}

			// Default
			$this->last = $this->last->add(Factory_Inline($line));
		}
	}

	function getAnchor($text, $level)
	{
		global $_symbol_anchor;

		// Heading id (auto-generated)
		$autoid = 'content_' . $this->id . '_' . $this->count;
		$this->count++;

		// Heading id (specified by users)
		$anchor = '';
		$id = make_heading($text, FALSE); // Cut fixed-anchor from $text
		if ($id == '') {
			// Not specified
			$id     = $autoid;
		} else {
			// 見出し位置の自動生成アンカー
			if (WikiConfig::getAutoHeadingAnchorVisibility()){
				// プラグインの出力制限がある場合はアンカーを出力しない
				$dispOffPlugin = WikiConfig::getDispOffPlugin();
				if (empty($dispOffPlugin)) $anchor = ' &aname(' . $id . ',super,full){' . $_symbol_anchor . '};';
			} else {			// 非表示の場合
				$anchor = ' &aname(' . $id . ',super,full,hidden){' . $_symbol_anchor . '};';
			}
		}

		$text = ' ' . $text;

		// Add 'page contents' link to its heading
		$this->contents_last = $this->contents_last->add(new Contents_UList($text, $level, $id));

		// Add heding
	//	return array($text . $anchor, $this->count > 1 ? "\n" . $top : '', $autoid);
		return array($text . $anchor, '', $autoid);
	}

	function insert($obj)
	{
		if (is_a($obj, 'Inline')) $obj = $obj->toPara();
		return parent::insert($obj);
	}

	function toString()
	{
		$text = parent::toString();

		// #contents
		$text = preg_replace_callback('/<#_contents_>/', array($this, 'replace_contents'), $text);

		return $text . "\n";
	}

	function replace_contents($arr)
	{
		//$contents  = '<div class="contents">' . "\n" . '<a id="contents_' . $this->id . '"></a>' . "\n" . $this->contents->toString() . "\n" . '</div>' . "\n";
		$contents  = '<div class="wiki_content">' . "\n" . '<a id="wiki_content_' . $this->id . '"></a>' . "\n" . $this->contents->toString() . "\n" . '</div>' . "\n";
		return $contents;
	}
}

class Contents_UList extends ListContainer
{
//	function Contents_UList($text, $level, $id)
	function __construct($text, $level, $id)
	{
		// Reformatting $text
		// A line started with "\n" means "preformatted" ... X(
		make_heading($text);
		$text = "\n" . '<a href="#' . $id . '">' . $text . '</a>' . "\n";
		//parent::ListContainer('ul', 'li', '-', str_repeat('-', $level));
		parent::__construct('ul', 'li', '-', str_repeat('-', $level));
		$this->insert(Factory_Inline($text));
	}

	function setParent($parent)
	{
		global $_list_pad_str;

		parent::setParent($parent);
		$step   = $this->level;
		$margin = $this->left_margin;
		if (isset($parent->parent) && is_a($parent->parent, 'ListContainer')) {
			$step  -= $parent->parent->level;
			$margin = 0;
		}
		$margin += $this->margin * ($step == $this->level ? 1 : $step);
		$this->style = sprintf($_list_pad_str, $this->level, $margin, $margin);
	}
}
?>
