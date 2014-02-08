<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfMoneyTaxCalculator
 *
 * @package    Sift
 * @subpackage money
 */
class sfMoneyTaxCalculator implements sfIMoneyTaxCalculator
{
    /**
     * Factory
     *
     * @param string $driver The driver (or empty for generic calculator)
     * @param array  $array  Array of options for the driver
     *
     * @return sfIMoneyTaxCalculator
     * @throws InvalidArgumentException If calculator for given country does not exist.
     * @throws LogicException If the calculator does not implement sfIMoneyTaxCalculator interface
     */
    public static function factory($driver = '', $options = array())
    {
        if (!empty($driver)) {
            $class = sprintf('sfMoneyTaxCalculatorDriver%s', $driver);
            if (!class_exists($class)) {
                throw new InvalidArgumentException(sprintf('Tax calculator driver class "%s" does not exist.', $class));
            }
        } else {
            $class = 'sfMoneyTaxCalculator';
        }

        $object = new $class($options);
        if (!$object instanceof sfIMoneyTaxCalculator) {
            throw new LogicException(sprintf(
                'The calculator "%s" instance does not implement sfIMoneyTaxCalculator interface.',
                $class
            ));
        }

        return $object;
    }

    /**
     * Returns the tax amount from the given $priceWithTax and $tax
     *
     * @param string $priceWithTax The price with tax
     * @param string $tax          The tax percentage
     * @param integer $scale       Scale of the amount
     * @param string $roundingMode Rounding mode
     */
    public function getTaxAmount(
        sfIMoneyCurrencyValue $priceWithTax,
        $tax,
        $scale = null,
        $roundingMode = sfRounding::HALF_EVEN
    ) {
        $coefficient = sfMath::divide($tax, sfMath::add('100', $tax), 100);

        $amount = sfRounding::round(
            sfMath::multiply($priceWithTax->getAmount(), $coefficient, 10),
            !is_null($scale) ? $scale : $priceWithTax->getCurrency()->getScale(),
            $roundingMode
        );

        return new sfMoneyCurrencyValue(sfMath::clean($amount), $priceWithTax->getCurrency());
    }

    /**
     * Returns price with tax
     *
     * @param sfMoneyCurrencyValue $price
     * @param string               $tax
     * @param integer              $scale
     * @param string               $roundingMode
     *
     * @return sfMoneyCurrencyValue
     */
    public function getPriceWithTax(
        sfIMoneyCurrencyValue $price,
        $tax,
        $scale = null,
        $roundingMode = sfRounding::HALF_EVEN
    ) {
        $amount = sfMath::multiply(
            $price->getAmount(),
            sfMath::add(1, sfMath::divide($tax, '100', 10), strlen($tax)),
            10
        );

        return new sfMoneyCurrencyValue(
            sfRounding::round($amount, !is_null($scale) ? $scale : $price->getCurrency()->getScale(), $roundingMode),
            $price->getCurrency());
    }

}
