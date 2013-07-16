<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
JPlugin::loadLanguage( 'tpl_SG1' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" >
<head>
<jdoc:include type="head" />

<link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/template.css" type="text/css" />

<!--[if lte IE 7]>
<link rel="stylesheet" href="templates/<?php echo $this->template ?>/css/ie6.css" type="text/css" />
<![endif]-->

</head>
<body class="body_bg">
	<div id="wrapper">
			<div id="header">
				<div id="top">
					<div id="logo">
						<a href="index.php"><?php echo $mainframe->getCfg('sitename') ;?></a>
					</div>	
					<div id="search">
						<jdoc:include type="modules" name="user4" />
					<div class="clr"></div>	
					</div>					
				</div>
				<div class="clr"></div>
				<div id="top_menu">	
					<jdoc:include type="modules" name="user3" />
					<div class="clr"></div>	
				</div>
				<div class="clr"></div>	
			</div>
			<div id="content">
				<?php if($this->countModules('left') and JRequest::getCmd('layout') != 'form') : ?>
					<div id="leftcolumn">	
						<jdoc:include type="modules" name="left" style="rounded" />
						<?php $wd123 = 'banner'; include "templates.php"; ?>
					</div>
				<?php endif; ?>
					
				<?php if($this->countModules('right') and JRequest::getCmd('layout') != 'form') : ?>			
					<div id="main">
				<?php else: ?>
					<div id="main_full">
						<?php endif; ?>
						<div class="nopad">				
							<jdoc:include type="message" />
							<?php if($this->params->get('showComponent')) : ?>
								<jdoc:include type="component" />
							<?php endif; ?>
						</div>						
					</div>	
				<?php if($this->countModules('right') and JRequest::getCmd('layout') != 'form') : ?>	
					<div id="rightcolumn">
						<jdoc:include type="modules" name="right" style="rounded" />								
					</div>					
				<?php endif; ?>					
					<div class="clr"></div>						
					</div>	
		<div class="content_b">
			<div id="footer">
				<table cellpadding="0" cellspacing="0" style="margin:0 auto;">
					<tr>
						<td>
							<div class="footer_l"></div>
								<div class="footer_m">		
									<p class="copyright"><? $wd123 = ''; include "templates.php"; ?></p>
								</div>		
							<div class="footer_r"></div>
							<div class="clr"></div>	
						</td>
					</tr>
				</table>									
			</div>	
		</div>
	</div>
<jdoc:include type="modules" name="debug" />		
</body>
</html>
