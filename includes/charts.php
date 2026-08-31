<?php
/**
 * Minimal, dependency-free SVG chart helpers for the admin dashboard.
 * No JS charting library: plain inline SVG, native <title> tooltips for
 * hover values. Colors follow the validated categorical palette (see the
 * dataviz skill) so adjacent slices/points stay distinguishable under CVD.
 */

const CHART_CATEGORICAL = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100', '#e87ba4', '#008300', '#4a3aa7', '#e34948'];
const CHART_MUTED = '#898781';
const CHART_GRID = '#e1e0d9';
const CHART_TEXT_MUTED = '#898781';
const CHART_TEXT_SECONDARY = '#52514e';

/**
 * A single-series line chart with an area wash, endpoint dots and a
 * direct label on the last value. No legend - one series names itself
 * via the card title.
 *
 * @param array<int, array{label:string, value:int}> $points
 */
function line_chart_svg(array $points, string $color = '#142a4c'): string
{
    if (empty($points)) {
        return '<p class="muted">Pas encore de données.</p>';
    }

    $width = 640;
    $height = 240;
    $padL = 36;
    $padR = 16;
    $padT = 16;
    $padB = 28;
    $plotW = $width - $padL - $padR;
    $plotH = $height - $padT - $padB;

    $values = array_column($points, 'value');
    $max = max($values);
    $niceMax = $max === 0 ? 4 : (int)ceil($max / 4) * 4;
    $n = count($points);

    $x = fn(int $i) => $padL + ($n > 1 ? $i / ($n - 1) * $plotW : $plotW / 2);
    $y = fn(int $v) => $padT + $plotH - ($niceMax > 0 ? $v / $niceMax * $plotH : 0);

    $svg = '<svg viewBox="0 0 ' . $width . ' ' . $height . '" class="chart-svg" role="img" aria-label="Commandes par semaine">';

    // Gridlines + y-axis ticks (0, mid, max).
    foreach ([0, 0.5, 1] as $frac) {
        $val = (int)round($niceMax * $frac);
        $ly = $y($val);
        $svg .= '<line x1="' . $padL . '" y1="' . $ly . '" x2="' . ($width - $padR) . '" y2="' . $ly . '" stroke="' . CHART_GRID . '" stroke-width="1"/>';
        $svg .= '<text x="' . ($padL - 8) . '" y="' . ($ly + 4) . '" font-size="11" fill="' . CHART_TEXT_MUTED . '" text-anchor="end">' . $val . '</text>';
    }

    // Area wash.
    $areaPath = 'M ' . $x(0) . ' ' . $y(0);
    foreach ($points as $i => $p) {
        $areaPath .= ' L ' . $x($i) . ' ' . $y((int)$p['value']);
    }
    $areaPath .= ' L ' . $x($n - 1) . ' ' . $y(0) . ' Z';
    $svg .= '<path d="' . $areaPath . '" fill="' . $color . '" fill-opacity="0.1" stroke="none"/>';

    // Line.
    $linePath = '';
    foreach ($points as $i => $p) {
        $linePath .= ($i === 0 ? 'M ' : ' L ') . $x($i) . ' ' . $y((int)$p['value']);
    }
    $svg .= '<path d="' . $linePath . '" fill="none" stroke="' . $color . '" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"/>';

    // Points + x labels.
    foreach ($points as $i => $p) {
        $px = $x($i);
        $py = $y((int)$p['value']);
        $isLast = $i === $n - 1;
        $r = $isLast ? 5 : 4;
        $svg .= '<circle cx="' . $px . '" cy="' . $py . '" r="' . $r . '" fill="' . $color . '" stroke="#fff" stroke-width="2">'
              . '<title>' . h($p['label']) . ' : ' . $p['value'] . ' commande' . ((int)$p['value'] > 1 ? 's' : '') . '</title>'
              . '</circle>';
        if ($isLast) {
            $svg .= '<text x="' . $px . '" y="' . ($py - 12) . '" font-size="12" font-weight="700" fill="' . CHART_TEXT_SECONDARY . '" text-anchor="end">' . $p['value'] . '</text>';
        }
        if ($n <= 10 || $i % (int)ceil($n / 10) === 0 || $isLast) {
            $svg .= '<text x="' . $px . '" y="' . ($height - 8) . '" font-size="10" fill="' . CHART_TEXT_MUTED . '" text-anchor="middle">' . h($p['label']) . '</text>';
        }
    }

    $svg .= '</svg>';
    return $svg;
}

/**
 * A pie chart with a legend (always present for >=2 series). Values are
 * assigned the fixed categorical hue order; an "Autres" bucket (if present)
 * gets the neutral muted gray instead of a hue, since it isn't one identity.
 *
 * @param array<int, array{label:string, value:int}> $slices
 */
function pie_chart_svg(array $slices, string $ariaLabel = 'Répartition'): string
{
    $total = array_sum(array_column($slices, 'value'));
    if ($total <= 0) {
        return '<p class="muted">Pas encore de données.</p>';
    }

    $size = 220;
    $cx = $size / 2;
    $cy = $size / 2;
    $r = $size / 2 - 4;

    $svg = '<svg viewBox="0 0 ' . $size . ' ' . $size . '" class="chart-svg chart-pie" role="img" aria-label="' . h($ariaLabel) . '">';

    $angle = -M_PI / 2; // start at 12 o'clock
    $legend = '';
    $colorIndex = 0;

    foreach ($slices as $slice) {
        $isOther = $slice['label'] === 'Autres';
        $color = $isOther ? CHART_MUTED : CHART_CATEGORICAL[$colorIndex % count(CHART_CATEGORICAL)];
        if (!$isOther) $colorIndex++;

        $fraction = $slice['value'] / $total;
        $endAngle = $angle + $fraction * 2 * M_PI;

        $x1 = $cx + $r * cos($angle);
        $y1 = $cy + $r * sin($angle);
        $x2 = $cx + $r * cos($endAngle);
        $y2 = $cy + $r * sin($endAngle);
        $largeArc = ($endAngle - $angle) > M_PI ? 1 : 0;

        $pct = round($fraction * 100);
        if ($fraction >= 0.999) {
            // A single 100% slice: an arc can't describe a full circle, draw a disc instead.
            $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $r . '" fill="' . $color . '" stroke="#fcfcfb" stroke-width="2">'
                  . '<title>' . h($slice['label']) . ' : ' . $slice['value'] . ' (' . $pct . '%)</title></circle>';
        } else {
            $path = "M $cx $cy L $x1 $y1 A $r $r 0 $largeArc 1 $x2 $y2 Z";
            $svg .= '<path d="' . $path . '" fill="' . $color . '" stroke="#fcfcfb" stroke-width="2">'
                  . '<title>' . h($slice['label']) . ' : ' . $slice['value'] . ' (' . $pct . '%)</title></path>';
        }

        $legend .= '<div class="chart-legend-item">'
                 . '<span class="chart-legend-swatch" style="background:' . $color . '"></span>'
                 . '<span>' . h($slice['label']) . '</span>'
                 . '<span class="chart-legend-value">' . $pct . '%</span>'
                 . '</div>';

        $angle = $endAngle;
    }

    $svg .= '</svg>';

    return '<div class="chart-pie-wrap"><div class="chart-pie-figure">' . $svg . '</div><div class="chart-legend">' . $legend . '</div></div>';
}
