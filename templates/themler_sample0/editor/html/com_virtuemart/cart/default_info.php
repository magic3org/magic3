<?php
defined('_JEXEC') or die;
?>
<?php /*BEGIN_EDITOR_OPEN*/
$app = JFactory::getApplication('site');
$templateName = $app->getTemplate();

$ret = false;
$templateDir = JPATH_THEMES . '/' . $templateName;
$editorClass = $templateDir . '/app/' . 'Editor.php';

if (!$app->isAdmin() && file_exists($editorClass)) {
    require_once $templateDir . '/app/' . 'Editor.php';
    $ret = DesignerEditor::override($templateName, __FILE__);
}

if ($ret) {
    $editorDir = $templateName . '/editor';
    require($ret);
    return;
} else {
/*BEGIN_EDITOR_CLOSE*/ ?>
<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'functions.php';
?>
<br />
<div class="data-control-id-3499 bd-shoppingcartgrandtotal-1 cart-totals grand-totals">
    <table class="data-control-id-3466 bd-table-4">
        <thead>
            <tr>
                <th colspan="2"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (VmConfig::get ('show_tax')) : ?>
            <tr>
                <td><?php  echo "<span  class='priceColor2'>" . JText::_ ('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT') ?></td>
                <td>
                    <?php
                        $text = $this->currencyDisplay->createPriceDiv ('billTaxAmount', '', $this->cart->pricesUnformatted['billTaxAmount'], FALSE);
                        echo "<span  class='priceColor2'>" . (!empty($text) ? $text : '-') . "</span>"
                    ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td><?php echo "<span  class='priceColor2'>" . JText::_ ('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') ?></td>
                <td>
                    <?php
                        $text = $this->currencyDisplay->createPriceDiv ('billDiscountAmount', '', $this->cart->pricesUnformatted['billDiscountAmount'], FALSE);
                        echo "<span  class='priceColor2'>" . (!empty($text) ? $text : '-') . "</span>"
                    ?>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="data-control-id-3498 bd-container-35 bd-tagstyles">
                <td>
                    <strong><?php echo JText::_ ('COM_VIRTUEMART_CART_TOTAL') ?>:</strong>
                </td>
                <td>
                    <strong><span><?php echo $this->currencyDisplay->createPriceDiv ('billTotal', '', $this->cart->pricesUnformatted['billTotal'], FALSE); ?></span></strong>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
<?php /*END_EDITOR_OPEN*/ } /*END_EDITOR_CLOSE*/ ?>