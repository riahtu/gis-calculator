<?php

namespace GisCalculator\Modules;

use \GisCalculator\Core\Metric;
use GisCalculator\Core\SettingsKeys;
use \GisCalculator\Element\Point;;

/**
 * Class Distance
 * @package GisCalculator\Modules
 *
 * This use circle formula
 * (x - x')^2 + (y - y')^2 <= R^2
 */
class Distance extends Module
{
    /**
     * Module name
     * @var string
     */
    protected $name = 'distance';

    /**
     * Current version
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * This Krasovsky's ellipsoid constant
     */
    private const ELLIPSOID = 6378245;

    /**
     * @param Point $from
     * @param Point $to
     * @return float
     */
    public function get(Point $from, Point $to): float
    {
        $angle = function(float $from, float $to) {
            $difference = ($from - $to) / 2;
            return pow(sin(deg2rad($difference)), 2);
        };

        $x = $angle($from->getLatitude(), $to->getLatitude());
        $y = $angle($from->getLongitude(), $to->getLongitude());

        $circle = ($x + cos(deg2rad($from->getLatitude())) * cos(deg2rad($to->getLatitude())) * $y);
        $distance = 2 * asin(sqrt($circle)) * self::ELLIPSOID;

        return $this->prepareResult($distance);
    }

    /**
     * @param float $result
     * @return float
     */
    private function prepareResult(float $result): float
    {
        $round = $this->settings->getValue(SettingsKeys::ROUND);
        $precision = 2;

        if (null !== $round) {
            $precision = (int) $round;
        }

        $result = round($result, $precision);

        $metric = $this->settings->getValue(SettingsKeys::METRIC);
        switch ($metric) {
            case Metric::CENTIMETERS:
                $result = $result * 100;
                break;
            case Metric::KILOMETERS:
                $result = $result / 1000;
                break;
            default:
                // Default as meters
                break;
        }

        return $result;
    }
}