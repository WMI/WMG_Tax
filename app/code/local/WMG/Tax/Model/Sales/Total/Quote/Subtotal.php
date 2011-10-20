<?php
/**
 * @category    WMG
 * @package     WMG_Tax
 * @copyright   Copyright (c) 2011 Warner Music Group. (http://www.wmg.com)
 * @author      Lee Bolding <lee.bolding@wmg.com>
 */

/**
 * Calculate items and address amounts including/excluding tax
 */
class WMG_Tax_Model_Sales_Total_Quote_Subtotal extends Mage_Tax_Model_Sales_Total_Quote_Subtotal
{
    /**
     * Calculate item price and row total including/excluding tax based on unit price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     * @return Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _unitBaseCalculation($item, $request)
    {
        $rate   = $this->_calculator->getRate($request->setProductClassId($item->getProduct()->getTaxClassId()));
        $qty    = $item->getTotalQty();

        $price          = $taxPrice         = $item->getCalculationPrice();
        $basePrice      = $baseTaxPrice     = $item->getBaseCalculationPrice();
        $subtotal       = $taxSubtotal      = $item->getRowTotal();
        $baseSubtotal   = $baseTaxSubtotal  = $item->getBaseRowTotal();
        $taxOnOrigPrice = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origPrice       = $item->getOriginalPrice();
            $baseOrigPrice   = $item->getBaseOriginalPrice();
        }


        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_areTaxRequestsSimilar) {
                $tax            = $this->_calculator->calcTaxAmount($price, $rate, true);
                $baseTax        = $this->_calculator->calcTaxAmount($basePrice, $rate, true);
                $taxPrice       = $price;
                $baseTaxPrice   = $basePrice;
                $taxSubtotal    = $subtotal;
                $baseTaxSubtotal= $baseSubtotal;
                $price          = $price - $tax;
                $basePrice      = $basePrice - $baseTax;
                $subtotal       = $price * $qty;
                $baseSubtotal   = $basePrice * $qty;
                if ($taxOnOrigPrice) {
                    $taxable        = $origPrice;
                    $baseTaxable    = $baseOrigPrice;
                } else {
                    $taxable        = $taxPrice;
                    $baseTaxable    = $baseTaxPrice;
                }
                $isPriceInclTax = true;
            } else {
                // LDB - recalculate all prices by first subtracting tax at new rate
                // previously, subtracted tax at OLD rate (which is WRONG)
                $storeRate      = $rate;
                $storeTax       = $this->_calculator->calcTaxAmount($price, $rate, true);
                $baseStoreTax   = $this->_calculator->calcTaxAmount($basePrice, $rate, true);
                
                $price          = $price - $storeTax;
                $basePrice      = $basePrice - $baseStoreTax;
                $subtotal       = $price * $qty;
                $baseSubtotal   = $basePrice * $qty;

                $tax            = $this->_calculator->calcTaxAmount($price, $rate, false);
                $baseTax        = $this->_calculator->calcTaxAmount($basePrice, $rate, false);
                $taxPrice       = $price + $tax;
                $baseTaxPrice   = $basePrice + $baseTax;
                $taxSubtotal    = $taxPrice * $qty;
                $baseTaxSubtotal= $baseTaxPrice * $qty;
                if ($taxOnOrigPrice) {
                    $taxable        = $origPrice - $storeTax;
                    $baseTaxable    = $baseOrigPrice - $baseStoreTax;
                } else {
                    $taxable        = $price;
                    $baseTaxable    = $basePrice;
                }
                $isPriceInclTax = false;
            }
        } else {
            $tax            = $this->_calculator->calcTaxAmount($price, $rate, false);
            $baseTax        = $this->_calculator->calcTaxAmount($basePrice, $rate, false);
            $taxPrice       = $price + $tax;
            $baseTaxPrice   = $basePrice + $baseTax;
            $taxSubtotal    = $taxPrice * $qty;
            $baseTaxSubtotal= $baseTaxPrice * $qty;
            if ($taxOnOrigPrice) {
                $taxable        = $origPrice;
                $baseTaxable    = $baseOrigPrice;
            } else {
                $taxable        = $price;
                $baseTaxable    = $basePrice;
            }
            $isPriceInclTax = false;
        }

        if ($item->hasCustomPrice()) {
            /**
             * Initialize item original price before declaring custom price
             */
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        } else {
            $item->setConvertedPrice($price);
        }
        $item->setPrice($basePrice);
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxPrice);
            $item->setBaseDiscountCalculationPrice($baseTaxPrice);
        }
        return $this;
    }

    /**
     * Calculate item price and row total including/excluding tax based on row total price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     * @return Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _rowBaseCalculation($item, $request)
    {
        $rate   = $this->_calculator->getRate($request->setProductClassId($item->getProduct()->getTaxClassId()));
        $qty    = $item->getTotalQty();

        $price          = $taxPrice         = $item->getCalculationPrice();
        $basePrice      = $baseTaxPrice     = $item->getBaseCalculationPrice();
        $subtotal       = $taxSubtotal      = $item->getRowTotal();
        $baseSubtotal   = $baseTaxSubtotal  = $item->getBaseRowTotal();
        $taxOnOrigPrice = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origSubtotal       = $item->getOriginalPrice() * $qty;
            $baseOrigSubtotal   = $item->getBaseOriginalPrice() * $qty;
        }

        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_areTaxRequestsSimilar) {
                $rowTax         = $this->_calculator->calcTaxAmount($subtotal, $rate, true);
                $baseRowTax     = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, true);
                $taxPrice       = $price;
                $baseTaxPrice   = $basePrice;
                $taxSubtotal    = $subtotal;
                $baseTaxSubtotal= $baseSubtotal;
                $subtotal       = $subtotal - $rowTax;
                $baseSubtotal   = $baseSubtotal - $baseRowTax;
                $price          = $this->_calculator->round($subtotal/$qty);
                $basePrice      = $this->_calculator->round($baseSubtotal/$qty);
                if ($taxOnOrigPrice) {
                    $taxable        = $origSubtotal;
                    $baseTaxable    = $baseOrigSubtotal;
                } else {
                    $taxable        = $taxSubtotal;
                    $baseTaxable    = $baseTaxSubtotal;
                }
                $isPriceInclTax = true;
            } else {
                // LDB - recalculate all prices by first subtracting tax at new rate
                // previously, subtracted tax at OLD rate (which is WRONG)
                $storeRate      = $rate;
                $storeTax       = $this->_calculator->calcTaxAmount($subtotal, $rate, true);
                $baseStoreTax   = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, true);
                
                $subtotal       = $this->_calculator->round($subtotal - $storeTax);
                $baseSubtotal   = $this->_calculator->round($baseSubtotal - $baseStoreTax);
                $price          = $this->_calculator->round($subtotal/$qty);
                $basePrice      = $this->_calculator->round($baseSubtotal/$qty);

                $rowTax         = $this->_calculator->calcTaxAmount($subtotal, $rate, false);
                $baseRowTax     = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, false);
                $taxSubtotal    = $subtotal + $rowTax;
                $baseTaxSubtotal= $baseSubtotal + $baseRowTax;
                $taxPrice       = $this->_calculator->round($taxSubtotal/$qty);
                $baseTaxPrice   = $this->_calculator->round($baseTaxSubtotal/$qty);
                if ($taxOnOrigPrice) {
                    $taxable        = $this->_calculator->round($origSubtotal - $storeTax);
                    $baseTaxable    = $this->_calculator->round($baseOrigSubtotal - $baseStoreTax);
                } else {
                    $taxable        = $subtotal;
                    $baseTaxable    = $baseSubtotal;
                }
                $isPriceInclTax = false;
            }
        } else {
            $rowTax     = $this->_calculator->calcTaxAmount($subtotal, $rate, false);
            $baseRowTax = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, false);
            $taxSubtotal    = $subtotal + $rowTax;
            $baseTaxSubtotal= $baseSubtotal + $baseRowTax;
            $taxPrice       = $this->_calculator->round($taxSubtotal/$qty);
            $baseTaxPrice   = $this->_calculator->round($baseTaxSubtotal/$qty);
            if ($taxOnOrigPrice) {
                $taxable        = $origSubtotal;
                $baseTaxable    = $baseOrigSubtotal;
            } else {
                $taxable        = $subtotal;
                $baseTaxable    = $baseSubtotal;
            }
            $isPriceInclTax = false;
        }

        if ($item->hasCustomPrice()) {
            /**
             * Initialize item original price before declaring custom price
             */
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        } else {
            $item->setConvertedPrice($price);
        }
        $item->setPrice($basePrice);
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxSubtotal/$qty);
            $item->setBaseDiscountCalculationPrice($baseTaxSubtotal/$qty);
        } elseif ($isPriceInclTax) {
            $item->setDiscountCalculationPrice($subtotal/$qty);
            $item->setBaseDiscountCalculationPrice($baseSubtotal/$qty);
        }

        return $this;
    }

    /**
     * Calculate item price and row total including/excluding tax based on total price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     * @return Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _totalBaseCalculation($item, $request)
    {
        $calc   = $this->_calculator;
        $rate   = $calc->getRate($request->setProductClassId($item->getProduct()->getTaxClassId()));
        $qty    = $item->getTotalQty();

        $price          = $taxPrice         = $item->getCalculationPrice();
        $basePrice      = $baseTaxPrice     = $item->getBaseCalculationPrice();
        $subtotal       = $taxSubtotal      = $item->getRowTotal();
        $baseSubtotal   = $baseTaxSubtotal  = $item->getBaseRowTotal();
        $taxOnOrigPrice = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origSubtotal       = $item->getOriginalPrice() * $qty;
            $baseOrigSubtotal   = $item->getBaseOriginalPrice() * $qty;
        }
        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_areTaxRequestsSimilar) {
                $rowTax         = $this->_deltaRound($calc->calcTaxAmount($subtotal, $rate, true, false), $rate, true);
                $baseRowTax     = $this->_deltaRound($calc->calcTaxAmount($baseSubtotal, $rate, true, false), $rate, true, 'base');
                $taxPrice       = $price;
                $baseTaxPrice   = $basePrice;
                $taxSubtotal    = $subtotal;
                $baseTaxSubtotal= $baseSubtotal;
                $subtotal       = $subtotal - $rowTax;
                $baseSubtotal   = $baseSubtotal - $baseRowTax;
                $price          = $calc->round($subtotal/$qty);
                $basePrice      = $calc->round($baseSubtotal/$qty);
                if ($taxOnOrigPrice) {
                    $taxable        = $origSubtotal;
                    $baseTaxable    = $baseOrigSubtotal;
                } else {
                    $taxable        = $taxSubtotal;
                    $baseTaxable    = $baseTaxSubtotal;
                }
                $isPriceInclTax = true;
            } else {
                // LDB - recalculate all prices by first subtracting tax at new rate
                // previously, subtracted tax at OLD rate (which is WRONG)
                $storeRate      = $rate;
                $storeTax       = $this->_calculator->calcTaxAmount($subtotal, $rate, true);
                $baseStoreTax   = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, true);
                
                $subtotal       = $calc->round($subtotal - $storeTax);
                $baseSubtotal   = $calc->round($baseSubtotal - $baseStoreTax);
                $price          = $calc->round($subtotal/$qty);
                $basePrice      = $calc->round($baseSubtotal/$qty);

                $rowTax         = $this->_deltaRound($calc->calcTaxAmount($subtotal, $rate, false, false), $rate, true);
                $baseRowTax     = $this->_deltaRound($calc->calcTaxAmount($baseSubtotal, $rate, false, false), $rate, true, 'base');
                $taxSubtotal    = $subtotal + $rowTax;
                $baseTaxSubtotal= $baseSubtotal + $baseRowTax;
                $taxPrice       = $calc->round($taxSubtotal/$qty);
                $baseTaxPrice   = $calc->round($baseTaxSubtotal/$qty);
                if ($taxOnOrigPrice) {
                    $taxable        = $calc->round($origSubtotal - $storeTax);
                    $baseTaxable    = $calc->round($baseOrigSubtotal - $baseStoreTax);
                } else {
                    $taxable        = $subtotal;
                    $baseTaxable    = $baseSubtotal;
                }
                $isPriceInclTax = false;
            }
        } else {
            $rowTax         = $this->_deltaRound($calc->calcTaxAmount($subtotal, $rate, false, false), $rate, true);
            $baseRowTax     = $this->_deltaRound($calc->calcTaxAmount($baseSubtotal, $rate, false, false), $rate, true, 'base');
            $taxSubtotal    = $subtotal + $rowTax;
            $baseTaxSubtotal= $baseSubtotal + $baseRowTax;
            $taxPrice       = $calc->round($taxSubtotal/$qty);
            $baseTaxPrice   = $calc->round($baseTaxSubtotal/$qty);
            if ($taxOnOrigPrice) {
                $taxable        = $origSubtotal;
                $baseTaxable    = $baseOrigSubtotal;
            } else {
                $taxable        = $subtotal;
                $baseTaxable    = $baseSubtotal;
            }
            $isPriceInclTax = false;
        }

        if ($item->hasCustomPrice()) {
            /**
             * Initialize item original price before declaring custom price
             */
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        } else {
            $item->setConvertedPrice($price);
        }
        $item->setPrice($basePrice);
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxSubtotal/$qty);
            $item->setBaseDiscountCalculationPrice($baseTaxSubtotal/$qty);
        } elseif ($isPriceInclTax) {
            $item->setDiscountCalculationPrice($subtotal/$qty);
            $item->setBaseDiscountCalculationPrice($baseSubtotal/$qty);
        }
        return $this;
    }
}
