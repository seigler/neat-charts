<?php
/**
 * Method 1 - you can call this file directly to display chart eg.
 * chart.php?data=X,Y;Jan,10;Feb,15;Mar,20;Apr,25;May,30&valShow=1
 * chart.php?data=Serial,X,Y;SIDU,Jan,10;SIDU,Feb,20;BSB,Jan,15;BSB,Feb,25
if (isset($_GET['data'])) {
    $init = $_GET;
    unset($init['data']);
    if (!isset($init['fmt'])) $init['fmt'] = 'str';
    if (!isset($init['css'])) $init['css'] = 1;
    // you can add ip or referrer limit here ...
    //if ($_SERVER['REMOTE_ADDR'] <> '1.2.3.4') exit;
    //if ($_SERVER['HTTP_REFERER'] <> 'topnew.net') exit; // etc
    cms_chart($_GET['data'], $init);
}
**** disabled for security reasons
 */

/**
 * Topnew CMS Chart v5.5.5 - Released on 2016.05.05
 * The MIT License (MIT) Copyright (c) 1998-2015, Topnew Geo, topnew.net/chart
 * Draw an SVG chart (bar, barV, barS, line or pie), responsive to parent box
 *
 * Method 2 - include this file and call cms_chart() in your php file
 *
 * @param array $data = array() or string -- check cms_chart_data() for details
 * @param array $init = array() as following:
 * chart = bar [default], line, pie, barV, barS
 * w = px of SVG width default 480px. Tip: bigger w will produce fine charts
 * w = fix : will auto set w according to number of bar/line
 * h = px of SVG height default 250px. Pie chart will auto set h according to w
 * gapT, gapR, gapB, gapL = px of gaps on Top, Right, Bottom and Left
 * title = title of the chart
 * xTitle = title on axisX
 * yTitle = title on axisY
 * titleAlign, xTitleAlign, yTitleAlign = 1 Left 2 Center [default] 3 Right
 * xSkip = 5 means skip 5 labels between each two
 * xSkipMax = 50 means skip floor(xNum / 50)
 * xSkip = -5 when negative it means substr(xKey,-2) % -5 is true eg 5 10 15 ...
 * legend = T(op), R(ight) [default], B(ottom), 0 none
 * legendW = px of legend width
 * yUnit = additional text after axisY label
 * xFormat, yFormat = php . substr|fm|to or format|decimal|,|. or date|M
 * xKey = year, month, week, day, hour
 * xMin, xMax default to data min and max
 * sort = y (sort on axisY labels, or yVal for only one serial chart)
 * sort = x (sort on axisX labels), '' default none
 * color = fffccc,cccfff : add 2 color at front
 * colorAdd = fffccc,cccfff : add 2 color at end
 * colorDel = 0,2 : delete color #0 and #2
 * ySum = 1 : sum up yVal while x moves, default 0
 * xSum = 8 (substr(xAxis,0,8)) or 5,2 or -2 to call php::substr
 * pieArc = 0 ~ 360 for pie : start Arc
 * pieStripe = 0 / 1 for pie
 * piePct = 0 / 1 for pie to include %
 * pieDonut = 0 / 1 pie or donut
 * css = 0 / 1 to cout default chart css style
 * style = '...' : to include customized css style to over-write default css
 * fmt = '' sxy xsy sxx xss, str str_***, sql sql_*** please check chart_data()
 * sepCol = , : data seperator to cut columns for str_*** data
 * sepRow = ; : data seperator to cut rows for str_*** data
 * valShow = 0 / 1 : show value
 * valAngle = -90 ~ 90
 * xAngle = -90 ~ 90
 *
 * TODO: barV with negative values, it is confusing and not displaying correctly
 * legendW looks like buggy
 * &init looks horrible -- need redesign
 * also negative value for pie is ignored
 * other para tobe added = font fontSize yAxis=2
 */
function cms_chart(&$data = null, $init = null) {
    $ceil = array('w', 'h', 'gapT', 'gapR', 'gapB', 'gapL', 'legendW', 'titleAlign', 'xTitleAlign', 'yTitleAlign', 'xSkip', 'xSkipMax', 'xAngle', 'ySum', 'pieArc', 'pieStripe', 'piePct', 'pieDonut', 'css', 'valAngle', 'valShow');
    $trim = array('chart', 'fmt', 'title', 'legend', 'style', 'sepCol', 'sepRow', 'xTitle', 'yTitle', 'yUnit', 'xFormat', 'yFormat', 'xMin', 'xMax', 'xKey', 'sort', 'xSum');
    foreach ($ceil as $k) $arr[$k] = isset($init[$k]) ? ceil($init[$k]) : 0;
    foreach ($trim as $k) $arr[$k] = isset($init[$k]) ? trim($init[$k]) : '';
    $data = cms_chart_data($data, $arr['fmt'], $arr['sort'], $arr['xKey'], $arr['xMin'], $arr['xMax'], $arr['sepRow'], $arr['sepCol'], $arr['ySum'], 'barS' === $arr['chart']);
    if (!$data) return;
    $color = cms_chart_color($init);
    $init = cms_chart_init($arr, $data, 'fix' === (isset($init['w']) ? $init['w'] : ''));
    extract($init);
    $init['color'] = $color;
    echo "\n" . '<svg viewBox="0 0 '. "$w $h" .'" class="chart" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink= "http://www.w3.org/1999/xlink">';
    cms_chart_css($css, $style);
    echo "\n" . '  <rect width="'. $w .'" height="'. $h .'" class="chart-bg"></rect>';
    echo "\n" . '  <rect x="'. $x1 .'" y="'. $y1 .'" width="'. $x2 .'" height="'. $y2 .'" class="chart-box"></rect>';
    $is_pie = ('pie' === $chart);
    if (!$is_pie) {
        $is_bar = ('bar' === substr($chart, 0, 3));
        $is_barV = ('barV' === $chart);
        cms_chart_axisX($data, $x1, $y1, $x2, $y2, $xVal, $xSkip, $xFormat, $is_bar, $xAngle, $yUnit, $yFormat, $color, $mouseInfo);
        cms_chart_axisY($data, $x1, $y1, $x2, $y2, $yVal, $yUnit, $yFormat, $is_barV);
        $zoom = $y2 / abs($yVal[0] - end($yVal));
        $init['xVal'] = $xVal;
        $init['yVal'] = $yVal;
    }
    cms_chart_title($y1, $y2, $w, $title, $titleAlign, $xTitle, $xTitleAlign, $yTitle, $yTitleAlign, $is_pie);
    if ($is_pie) {
        $data = reset($data);
        foreach ($data as $k => $v) {
            if ($v < 0 || !$v) unset($data[$k]);
        }
    }
    cms_chart_legend($data, $color, $legend, $legendW, $x1, $y1, $x2, $y2, $xTitle, $is_pie, $piePct);
    echo "\n  <!-- the chart -->\n" . '  <g transform="translate('. "$x1,$y1" .')">';
    if ('line' == $chart) cms_chart_line($data, $color, $zoom, $xVal, $yVal[0], $valShow, $valAngle, $yFormat);
    elseif ('pie' == $chart) cms_chart_pie($data, $color, round($y2 / 2, 5), $pieArc, $pieStripe, $piePct, $pieDonut, $valShow, $yFormat);
    else cms_chart_bar($data, $color, $zoom, $xVal, $yVal[0], $is_barV, 'barS' === $chart, $valShow, $valAngle, $yFormat);
    echo "\n  </g>";
    if (!$is_pie) echo $mouseInfo;
    echo "\n</svg>\n";
}
/**
 * ---------------------------------------------------------------------------
 * data fmt = '' || sxy, xsy, xss, sxx (all as array)
 * ---------------------------------------------------------------------------
 * fmt = '' or sxy (default) : col1 = serial, col2 = xAxis, col3 = yVal
 * max 3 cols, col4+ will be ignored
 * eg. select manager,month,count(*) qty from sales group by 1,2
 * Tom Jan 123
 * Sam May 234
 * data = cms_arr(sql,2) dirty or cms_arr(sql,2,1) clean
 *
 * fmt = xsy : col1 = xAxis, col2 = serial, col3 = yVal
 * max 3 cols, col4+ will be ignored
 * eg. select month,manager,count(*) qty from sales group by 1,2
 * Jan Tom 123
 * May Sam 234
 * data = cms_arr(sql,2) dirty or cms_arr(sql,2,1) clean
 * -- as a result this data same as xss
 *
 * fmt = xss : col1 = xAxis, col2...x = serial, yVal in the grid
 * eg. select month,count(*) qty,sum(amt) amt from sales group by 1
 * Jan 123 956 789 ...
 * Feb 222 933 444 ...
 * data = cms_arr(sql,1) -- only this need to convert to default sxy
 *
 * fmt = sxx : col1 = serial, col2...x = xAxis, yVal in the grid
 * very rarely when the col name is xAxis
 * eg. select manager,jan,feb,march from rep_sales
 * Tom 123 456 789 ...
 * Sam 222 333 444 ...
 * data = cms_arr(sql,1) -- data same as default sxy
 *
 * when there is only 2 cols, which means only 1 serial
 * no matter which fmt : col1 = xAxis, col2 = yVal
 * eg. select month,count(*) qty from sales group by 1
 * Jan 123
 * Feb 234
 * data = cms_row(sql,2) = cms_arr(sql,1,1) or dirty cms_arr(sql,1)
 * ---------------------------------------------------------------------------
 * as a result, if you can not remember which func to get data, remember this:
 * data = cms_arr(sql,2) : when fmt got 3 different chars eg xsy sxy
 * data = cms_arr(sql,1) : when fmt only has 2 different chars eg xss sxx
 *
 * ---------------------------------------------------------------------------
 * fmt = str || str_sxy, str_xsy, str_xss, str_sxx (all as string)
 * ---------------------------------------------------------------------------
 * default sepCol = , sepRow = ;
 *
 * str_sxy = 'Staff,month,Qty;Tom,Feb,12;Tom,Mar,34;Sam,Jan,112;...'
 * str_xsy = 'month,Staff,Qty;Feb,Tom,12;Mar,Tom,34;Jan,Sam,112;...'
 * str_xss = 'month,Qty,Vol;Feb,22,12;Mar,31,34;May,12,112;...'
 * str_sxx = 'Staff,Jan,Feb,Mar;Tom,1,2,3;Sam,2,3,4;...'
 *
 * ---------------------------------------------------------------------------
 * fmt = sql || sql_sxy, sql_xsy, sql_xss, sql_sxx (all as SQL)
 * ---------------------------------------------------------------------------
 * you need cms_arr() and cms_row() etc cms db function plugin to run this
 *
 * sql_sxy cms_arr(sql,2) select staff,year,count(*) from sales group by 1,2;
 * sql_xsy cms_arr(sql,2) select year,staff,count(*) from sales group by 1,2;
 * sql_xss cms_arr(sql,1) select year,count(*) qty,sum(amt) vol from sales group by 1;
 * sql_sxx cms_arr(sql,1) select staff,jan,feb,mar from rep_sales;
 *
 * if you only have 2 cols in sql, you have to set fmt = sql_sxx or sql_xss!!!
 * sql_*** cms_arr(sql,1) select a,b from tab -- only 2 cols
 */
function cms_chart_data($data, $fmt, $sort, $xKey, $xMin, $xMax, $sepRow, $sepCol, $ySum, $is_barS) {
    $fmt3 = substr($fmt, 0, 3);
    if ('sql' === $fmt3) {
        $fmt = substr($fmt, 4);
        $data = cms_arr($data, ('sxx' === $fmt || 'xss' === $fmt ? 1 : 2));
    } elseif ('str' === $fmt3) {
        $fmt = substr($fmt, 4);
        $data = cms_chart_data_str($data, $fmt, $sepRow, $sepCol);
    } elseif ('jso' === $fmt3) {
        $data = json_decode($data, 1);
    }
    $num = count($data);
    if (!$num) return;
    if (!is_array(reset($data))) {
        $data = array($data); // only 2 cols
        $num = 1;
    } elseif ('xss' === $fmt || 'xsy' === $fmt) {
        foreach ($data as $x => $arr) {
            foreach ($arr as $s => $y) $sxy[$s][$x] = $y;
        }
        $data = $sxy;
    }
    $keys = array(); // x labels
    foreach ($data as $s => $arr) {
        foreach ($arr as $x => $y) {
            if (!in_array($x, $keys)) $keys[] = $x;
            if (is_array($y)) $data[$s][$x] = reset($y); // clean dirty data
        }
    }
    if (in_array($xKey, array('year','month','week','day','hour'))) {
        $keys = cms_chart_data_ymdH($xKey, $keys, $xMin, $xMax);
    } elseif ('x' === $sort) {
        sort($keys);
    }
    if ('y' === $sort && $num > 1) ksort($data);
    foreach ($data as $k => $arr) {
        foreach ($keys as $k2) $res[$k][$k2] = isset($arr[$k2]) ? $arr[$k2] + 0 : 0;
    }
    if ('y' === $sort && 1 == $num) { // if pie make sure only 1 array
        $pie = reset($res);
        arsort($pie);
        $res = array($pie);
    }
    if ($ySum) {
        foreach ($res as $k => $arr) {
            $j = $hold = 0;
            foreach ($arr as $k2 => $v) {
                if ($j++) $res[$k][$k2] += $hold;
                $hold += $v;
            }
        }
    }
    if ($is_barS) {
        foreach ($res as $k => $arr) $sortS[$k] = array_sum($arr);
        array_multisort($sortS, SORT_DESC, $res);
    }
    return $res;
}
function cms_chart_data_str($str, $fmt, $sepRow, $sepCol) {
    if (!$sepRow) $sepRow = ';';
    if (!$sepCol) $sepCol = ',';
    $rows = explode($sepRow, trim($str));
    $head = explode($sepCol, trim(array_shift($rows)));
    foreach ($head as $v) $cols[] = trim($v);
    unset($cols[0]);
    if (1 === count($cols)) {
        foreach ($rows as $i => $r) {
            $r = explode($sepCol, trim($r));
            $data[trim($r[0])] = trim($r[1]);
        }
        return $data;
    }
    foreach ($rows as $i => $r) {
        $r = explode($sepCol, trim($r));
        foreach ($r as $j => $v) $r[$j] = trim($v);
        if ('xss' === $fmt || 'sxx' === $fmt) {
            foreach ($r as $j => $v) {
                if ($j) $data[$r[0]][$cols[$j]] = $v;
            }
        } else {
            $data[$r[0]][$r[1]] = isset($r[2]) ? $r[2] : 0;
        }
    }
    return $data;
}
function cms_chart_data_ymdH($xKey, $keys, $xMin, $xMax) {
    if (!$xMin) $xMin = min($keys);
    if (!$xMax) $xMax = max($keys);
    $y1 = substr($xMin, 0, 4); $y2 = substr($xMax, 0, 4);
    if ('year' === $xKey) return range($y1, $y2);
    $keys = array();
    if ('week' === $xKey) {
        $xMin = substr($xMin, 0, 6); $xMax = substr($xMax, 0, 6);
        for ($Y = $y1; $Y <= $y2; $Y++) { for ($W = 1; $W < 54; $W++) {
            $yw = $Y . str_pad($W, 2, 0, STR_PAD_LEFT);
            if ($yw >= $xMin && $yw <= $xMax) $keys[] = $yw;
        }} // year week
        return $keys;
    }
    $m1 = substr($xMin, 0, 7); $d1 = substr($xMin, 0, 10);
    $m2 = substr($xMax, 0, 7); $d2 = substr($xMax, 0, 10);
    for ($Y = $y1; $Y <= $y2; $Y++) { for ($M = 1; $M < 13; $M++) {
        $ym = $Y .'-'. str_pad($M, 2, 0, STR_PAD_LEFT);
        if ($ym >= $m1 && $ym <= $m2) {
            if ('month' === $xKey) $keys[] = $ym;
            else { for ($D = 1; $D < 32; $D++) {
                $ymd = $ym .'-'. str_pad($D, 2, 0, STR_PAD_LEFT);
                if (checkdate($M, $D, $Y) && $ymd >= $d1 && $ymd <= $d2) {
                    if ('day' === $xKey) $keys[] = $ymd;
                    else { for ($H = 0; $H < 24; $H++) {
                        $keys[] = $ymd .' '. str_pad($H, 2, 0, STR_PAD_LEFT);
                    }} // hour
                } // valid day
            }} // day
        } // valid m
    }} // y m
    return $keys;
}
function cms_chart_init($arr, $data, $wFix = 0) {
    $num = count($data);
    $xNum = count(reset($data));
    if (!$arr['xSkip'] && $arr['xSkipMax'] > 0) $arr['xSkip'] = floor($xNum / $arr['xSkipMax']);
    if (!$arr['chart'] || !in_array($arr['chart'], array('line','pie','barV','barS'))) $arr['chart'] = 'bar';
    $is_pie = ('pie' === $arr['chart']);
    $arr['gapL'] += 9; // default box margin 9px each side
    $arr['gapT'] += 9;
    $arr['gapR'] += 9;
    $arr['gapB'] += 9;
    if ($arr['title']) $arr['gapT'] += 15;
    if ($arr['xTitle']) $arr['gapB'] += 15;
    if ($arr['yTitle']) $arr['gapL'] += 15;
    if (!strlen($arr['legend'])) $arr['legend'] = ($num > 1 || $is_pie) ? 'R' : '0';
    if ($arr['legendW'] < 1) $arr['legendW'] = 80;
    if ($arr['legend']) { // 0 T B R
        if ('T' === $arr['legend']) $arr['gapT'] += 15;
        elseif ('B' === $arr['legend']) $arr['gapB'] += 15;
        elseif ('L' === $arr['legend']) $arr['gapL'] += $arr['legendW'];
        else $arr['gapR'] += $arr['legendW'];
    }
    if ($is_pie) {
        if ($arr['yTitle']) $arr['gapL'] += 3;
        if ('L' === $arr['legend']) $arr['gapL'] += 51;
        elseif ('R' === $arr['legend']) $arr['gapR'] += 51;
    } else {
        $arr['gapL'] += 51; // default yLabel
        $arr['gapB'] += 16; // default xLabel
    }
    $arr['x1'] = $arr['gapL'];
    $arr['y1'] = $arr['gapT'];
    if ($arr['x1'] < 0) $arr['x1'] = 0;
    if ($arr['y1'] < 0) $arr['y1'] = 0;

    if (!$is_pie && $wFix) {
        $arr['x2'] = 10 * $num * $xNum + $xNum + 1;
        $arr['w'] = $arr['x1'] + $arr['x2'] + $arr['gapR'];
    } else {
        if ($arr['w'] < 1) $arr['w'] = 480;
        $arr['x2'] = $arr['w'] - $arr['x1'] - $arr['gapR'];
        if ($arr['x2'] > $arr['w'] || $arr['x2'] < $arr['x1']) $arr['x2'] = $arr['w'];
    }
    if ($arr['h'] < 1) $arr['h'] = 250;
    if ($arr['h'] > $arr['w']) $arr['h'] = $arr['w'];
    if ($is_pie) {
        $arr['y2'] = $arr['x2'];
        $arr['h'] = $arr['y1'] + $arr['y2'] + $arr['gapB']; // pie h auto calculated
    } else {
        $arr['y2'] = $arr['h'] - $arr['y1'] - $arr['gapB'];
        if ($arr['y2'] > $arr['h'] || $arr['y2'] < $arr['y1']) $arr['y2'] = $arr['h'];
    }
    return $arr;
}
function cms_chart_color($init) {
    // the following are default 11 colors
    $defa = array('d9534f', 'f0ad4e', '5bc0de', '5cb85c', '337ab7', 'f26522', '754c24', 'd9ce00', '0e2e42', 'ce1797','672d8b');
    // add colors at front
    if (isset($init['color']) && !is_array($init['color'])) {
        $col = explode(',', $init['color']);
        if (is_array($col)) {
            foreach ($col as $c) {
                $c = trim(substr(trim($c), 0, 6));
                if (strlen($c) > 2) $color[] = $c;
            }
        }
    }
    // del colors
    if (isset($init['colorDel'])) {
        $col = explode(',', $init['colorDel']);
        if (is_array($col)) {
            foreach ($col as $c) {
                unset($defa[ceil($c)]);
            }
        }
    }
    foreach ($defa as $c) $color[] = $c;
    // add colors at end
    if (isset($init['colorAdd'])) {
        $col = explode(',', $init['colorAdd']);
        if (is_array($col)) {
            foreach ($col as $c) {
                $c = trim(substr(trim($c), 0, 6));
                if (strlen($c) > 2) $color[] = $c;
            }
        }
    }
    return $color;
}
function cms_chart_axisX($data, $x1, $y1, $x2, $y2, &$xVal, $xSkip, $xFormat, $is_bar, $xAngle, $yUnit, $yFormat, $color, &$mouseInfo) {
    $xNum = count(reset($data));
    if ($is_bar) {
        $xDiv = ($x2 - $xNum - 1) / $xNum;
        $xVal[0] = round($xDiv / 2 + 1, 5);
        $xDiv++;
        $xLeft = 0;
    } else {
        $xDiv = $x2 / ($xNum - 1);
        $xVal[0] = 0;
        $xLeft = $xDiv / 2;
    }
    $xLabel = array_keys(reset($data));
    if ($xAngle) {
        $angle1 = '<g transform="translate(5) rotate('. $xAngle .')">';// best for 45
        $angle2 = '</g>';
        $angleCSS = ' xAngle';
    } else {
        $angle1 = $angle2 = $angleCSS = '';
    }
    echo "\n" . '  <g class="chart-tick axisX'. $angleCSS .'" transform="translate('. $x1 .','. ($y1 + $y2) .')">';
    if ($yUnit) $yUnit = ' ' . $yUnit;
    $cNum = count($color);
    $j = 0;
    foreach ($data as $k => $arr) {
        $i = 0;
        foreach ($arr as $k2 => $v) {
            if ($i) $xVal[$i] = round($xVal[$i - 1] + $xDiv, 5);
            if (!isset($labelTxt[$i])) $labelTxt[$i] = '<tspan x="'. $xVal[$i] .'" dy="15">'. $k2 .'</tspan>';
            $labelTxt[$i] .= '<tspan x="'. $xVal[$i] .'" dy="15" fill="#'. $color[$j % $cNum] .'">'. ($k ? $k .' : ' : '') . cms_chart_axis_format($v, $yFormat) . $yUnit .'</tspan>';
            $i++;
        }
        $j++;
    }
    $mouseInfo = "\n" .'  <g class="chartInfo" transform="translate('. $x1 .','. $y1 .')">';
    $mouseInfoH= (count($data) + 1) * 15 + 7;
    for ($i = 0, $skip = 0; $i < $xNum; $i++) {
        $rect = '<rect x="'. round($i * $xDiv - $xLeft, 5) .'" width="'. round($xDiv, 5) .'" ';
        $mouseInfo .= "\n" .'    <g>'. $rect .'height="'. $y2 .'" fill="#000" opacity="0"/>'. $rect .'height="'. $mouseInfoH .'"/><text>'. $labelTxt[$i] .'</text></g>';
        if (!$xSkip || ($xSkip > 0 && $i == ($xSkip + 1) * $skip)
        || ($xSkip < 0 && !(substr($xLabel[$i], -2) % $xSkip))) {
            $skip++;
            echo "\n" . '    <g transform="translate('. $xVal[$i] .')"><line y2="-'. $y2 .'"></line>';
            echo $angle1 . '<text y="3" dy=".71em">'. cms_chart_axis_format($xLabel[$i], $xFormat) .'</text>' . $angle2;
            echo '</g>';
        }
    }
    $mouseInfo .= "\n  </g>";
    echo "\n" . '  </g>';
}
function cms_chart_axisY($data, $x1, $y1, $x2, $y2, &$yVal, $yUnit, $yFormat, $is_barV) {
    $max_min = reset($data);
    $max = $min = reset($max_min);
    if ($is_barV) {
        foreach ($data as $arr) {
            foreach ($arr as $k => $v) $TTL[0][$k] = $v + (isset($TTL[0][$k]) ? $TTL[0][$k] : 0);
        }
    } else $TTL = $data;
    foreach ($TTL as $arr) {
        $min = min(min($arr), $min);
        $max = max(max($arr), $max);
    }
    if ($max < 0 && $min < 0) $ttl = $min;
    elseif ($max < 0 || $min < 0) $ttl = $max - $min;
    else $ttl = $max;
    if ($ttl < 0) $ttl = abs($ttl);
    $zoom = pow(10, floor(log10($ttl)));
    $ttl /= $zoom;
    if ($ttl == 1) $step = 0.2;
    elseif ($ttl > 6) $step = 2;
    elseif ($ttl > 5) $step = 1.2;
    elseif ($ttl > 4) $step = 1;
    elseif ($ttl > 3) $step = 0.8;
    elseif ($ttl > 2) $step = 0.6;
    else $step = 0.4;
    if ($max <= 0 && $min < 0) {
        for ($i = 0; $i < 6; $i++) $yVal[] = 0 - $i * $zoom * $step;
    } else {
        for ($i = 5; $i >- 1; $i--) $yVal[] = $i * $zoom * $step;
        if ($max <= 0 || $min < 0) cms_chart_axisY_audit($max, $min, $step, $zoom, $yVal);
    }
    if ($max > 0 && $yVal[1] >= $max) array_shift($yVal);
    elseif ($min < 0 && $yVal[4] <= $min) array_pop($yVal);
    $step = count($yVal);
    if ($min < $yVal[$step-1]) $yVal[] = $yVal[$step-1] - abs($yVal[$step-2] - $yVal[$step-3]);
    $step = $y2 / (count($yVal) - 1);

    echo "\n" . '  <g class="chart-tick axisY" transform="translate('. "$x1,$y1" .')">';
    $yDiv = count($yVal);
    $last_not_zero = end($yVal);
    if ($yUnit) $yUnit = ' ' . $yUnit;
    for ($i = 0; $i < $yDiv; $i++) {
        echo "\n" . '    <g transform="translate(0,'. $i * $step .')"><line x2="'. $x2 .'"></line>';
        if ($yVal[$i] || $last_not_zero) {
            echo '<text x="-3" dy=".32em">'. cms_chart_axis_format($yVal[$i], $yFormat) . $yUnit . '</text>';
        }
        echo '</g>';
    }
    echo "\n" . '  </g>';
}
function cms_chart_axisY_audit($max, $min, $step, $zoom, &$yVal, $count = 0) {
    if ($count > 5) return;
    if ($yVal[1] > $max && $yVal[5] > $min) {
        $yVal[] = $yVal[5] - $zoom * $step;
        array_shift($yVal);
        cms_chart_axisY_audit($max, $min, $step, $zoom, $yVal, $count++);
    }
}
function cms_chart_axis_format($v, $format) {
    if (!$format) return $v;
    $format = explode('|', $format, 4); // eg substr|6 or format|0|.|, or data|M
    if ('format' == $format[0]) return number_format($v, isset($format[1]) ? ceil($format[1]) : 0, isset($format[2]) ? $format[2] : null, isset($format[3]) ? $format[3] : null);
    if ('substr' == $format[0]){
        if (isset($format[2])) return substr($v, ceil($format[1]), ceil($format[2]));
        return substr($v, isset($format[1]) ? ceil($format[1]) : 0);
    }
    if ('date' == $format[0]) return date($format[1], strtotime($v));
    return $v;
}
function cms_chart_title($y1, $y2, $w, $title, $titleAlign, $xTitle, $xTitleAlign, $yTitle, $yTitleAlign, $is_pie) {
    if ($title) {
        echo "\n" . '  <text y="15" x="';
        if (1 == $titleAlign) echo 5 . '"'; // left
        elseif (3 == $titleAlign) echo $w - 5 . '" text-anchor="end"'; // right
        else echo $w / 2 . '" text-anchor="middle"'; // default center
        echo '>'. $title .'</text>';
    }
    $y = $y1 + $y2;
    if ($xTitle) {
        echo "\n".'  <text y="'. ($y + ($is_pie ? 15 : 37)) .'" x="';
        if (1 == $xTitleAlign) echo 5 . '"'; // left
        elseif (3 == $xTitleAlign) echo $w - 5 . '" text-anchor="end"'; // right
        else echo $w / 2 . '" text-anchor="middle"'; // default center
        echo '>'. $xTitle .'</text>';
    }
    if ($yTitle) {
        echo "\n".'  <g transform="translate(15,'. $y .')"><text x="';
        if (1 == $yTitleAlign) echo 0 . '"'; // left
        elseif (3 == $yTitleAlign) echo $y2 . '" text-anchor="end"'; // right
        else echo $y2 / 2 . '" text-anchor="middle"'; // default center
        echo ' transform="rotate(-90)">'. $yTitle .'</text></g>';
    }
}
function cms_chart_legend($data, $color, $legend, $legendW, $x1, $y1, $x2, $y2, $xTitle, $is_pie, $piePct) {
    if (!$legend || (count($data) < 2 && !$is_pie)) return;
    $i = 0;
    if ('T' === $legend || 'B' === $legend) {
        $x = $legendW;
        $y = 0;
        if ('B' === $legend) $y1 += $y2 + ($is_pie ? 25 : 40) + ($xTitle ? 15 : 0);
    } else {
        $x = 0;
        $y = 16;
        $y1 += 15;
        if ('L' === $legend) $x1 = 9;
        else $x1 += $x2 + 8;
    }
    echo "\n" . '  <!-- legend -->';
    $cNum = count($color);
    foreach ($data as $k => $arr) {
        echo "\n" . '  <g fill="#'. $color[$i % $cNum] .'">';
        $cx = $x1 + 6 + $i * $x;
        $cy = $y1 - 10 + $i * $y;
        echo '<circle cx="'. $cx .'" cy="'. $cy .'" r="6"/><text x="'. ($cx + 12) .'" y="'. ($cy + 4) .'">'. $k .'</text>';
        echo '</g>';
        $i++;
    }
}
function cms_chart_bar($data, $color, $zoom, $xVal, $yVal0, $is_barV, $is_barS, $valShow, $valAngle, $yFormat) {
    $i = 0;
    $w1 = $xDiv = abs((isset($xVal[1]) ? $xVal[1] : 0) - $xVal[0] - 1);
    $half = $xDiv / 2;
    if (!$is_barV && !$is_barS) $w1 /= count($data);
    $w1 = round($w1, 5);
    $cNum = count($color);
    $is_barVS = $is_barV || $is_barS;
    $opacity = $is_barS ? ' opacity=".8"' : '';
    if ($valShow) {
        if (-90 == $valAngle) $xMove = 4;
        elseif ($valAngle > 0) $xMove = -3;
        else $xMove = 0;
        $yMove = (45 == $valAngle) ? 5 : 0;
        $valClass = ' class="valShow"';
    } else {
        $valClass = '';
    }
    foreach ($data as $arr) {
        echo "\n" . '    <g fill="#'. $color[$i % $cNum] .'"'. $valClass .'>';
        $j = 0;
        foreach ($arr as $v) {
            $hold[$i][$j] = $v;
            if ($is_barV) {
                // when more than 1 negative bar, barV does not work--fix later
                if ($i) $Y[$j] = (isset($Y[$j]) ? $Y[$j] : 0) + ($hold[$i-1][$j] > 0 ? $hold[$i-1][$j] : 0);
            } else $Y[$j] = 0;
            //if ($is_barV && $i) $Y[$j] += $hold[$i-1][$j];
            //else $Y[$j] = 0;
            $x = $xVal[$j] - $half + ($is_barVS ? 0 : $i * $w1);
            $y = $yVal0 - (isset($Y[$j]) ? $Y[$j] : 0);
            if ($v > 0) $y -= $v;
            $x = round($x, 5);
            $y = round($y * $zoom, 5);
            echo "\n".'      <rect'. $opacity .' x="'. $x .'" y="'. $y .'" width="'. $w1 .'" height="'. round(abs($v) * $zoom, 5) .'"/>';//remove inside rect: <title>'. $v .'</title>
            if ($valShow && $v) echo cms_chart_val($valAngle, $x + $w1 /2, $y, $xMove, $yMove, cms_chart_axis_format($v, $yFormat));
            $j++;
        }
        echo "\n" . '    </g>';
        $i++;
    }
}
function cms_chart_val($valAngle, $x, $y, $xMove, $yMove, $v) {
    if (!$valAngle) return '<text x="'. $x .'" y="'. ($y + 10) .'" text-anchor="middle">'. $v .'</text>';
    return "\n" .'      <g transform="translate('. ($x + $xMove) .','. ($y + $yMove) .') rotate('. $valAngle .')"><text>'. $v .'</text></g>';
}
function cms_chart_line($data, $color, $zoom, $xVal, $yVal0, $valShow, $valAngle, $yFormat) {
    $i = 0;
    $cNum = count($color);
    foreach ($data as $arr) {
        $j = 0;
        $line = $dot = '';
        foreach ($arr as $v) {
            $x = round($xVal[$j++], 5);
            $y = round(($yVal0 - $v) * $zoom, 5);
            $line .= ' '. $x .','. $y;
            $dot .= "\n" . '      <circle cx="'. $x .'" cy="'. $y .'" r="3"/>';//removed: <title>'. $v .'</title>
            if ($valShow) $dot .= cms_chart_val($valAngle, $x, $y - 15, 0, 0, cms_chart_axis_format($v, $yFormat));
        }
        echo "\n" . '    <g fill="#'. $color[$i % $cNum] .'">';
        echo "\n" . '      <path d="M'. substr($line, 1) .'" class="line" stroke-linejoin="round" stroke="#' . $color[$i++ % $cNum] .'"/>';
//        echo $dot;
        echo "\n" . '    </g>';
    }
}
function cms_chart_pie($data, $color, $R, $Arc, $stripe, $piePct, $pieDonut, $valShow, $yFormat) {
    $sum = array_sum($data);
    foreach ($data as $k => $v) $pct[$k] = round($v / $sum, 5);
    echo "\n" . '    <g transform="translate('. "$R,$R" .')" class="pie">';
    echo "\n" . '      <circle r="'. $R .'" class="pieBg"/>';
    $i = 0;
    $cNum = count($color);
    foreach ($pct as $k => $v) {
        $a = round($v * 360, 5);
        if (360 == $a) echo "\n" . '      <circle r="'. $R .'" fill="#' . $color[$i % $cNum] . '"/>';
        elseif ($a) {
            echo  "\n" . '      <g' . ($Arc ? ' transform="rotate('. $Arc .')"' : '') . ' fill="#' . $color[$i % $cNum] . '" stroke="#fff" stroke-width="1">';
            $r = deg2rad($a);
            $x = round(cos($r) * $R, 5);
            $y = round(sin($r) * $R, 5);
            echo '<path d="M0,0 L'. "$R,0 A$R,$R 0 " . ($a > 180 ? 1 : 0) . ",1 $x,$y z\"/>";
            if ($valShow) echo '<g transform="rotate('. ($a / 2) .')">'. cms_chart_val(0, round($R * 3 / 4, 2), 0, 0, 0, cms_chart_axis_format($data[$k], $yFormat) . ($piePct ? ' ( '. round($v * 100) .' % )' : '')) .'</g>';
            echo '</g>';
            $Arc += $a;
        }
        $i++;
    }
    if ($stripe) {
        $stripeCSS = 'style="fill:none;stroke:#fff;stroke-width:'. round($R * 2 / 11, 5) .';opacity:0.2"';
        echo "\n      <!-- stripes -->\n      " . '<circle r="'. round($R * 8 / 11, 5) .'" '. $stripeCSS .'/><circle r="'. round($R * 4 / 11, 5) .'" '. $stripeCSS .'/>';
        echo "\n      " . '<circle r="'. round($R / 11, 5) .'" style="fill:#fff;stroke:0;opacity:0.2"/>';
    }
    if ($pieDonut) echo "\n" . '      <circle r="'. round($R / 2) .'" class="pieBg"/>';//donus
    echo "\n" . '    </g>';
}
function cms_chart_css($css, $style) {
    if (!$css && !$style) return;
    echo '<style>';
    if ($css) echo '
<![CDATA[
svg.chart{display:block}
.chart-bg{fill:transparent;opacity:1}
.chart-box{fill:transparent;opacity:1}
.chart-tick line{stroke:#000;stroke-width:1;opacity:0.1}
.chart text{font-family:Helvetica,Arial,Verdana,sans-serif;font-size:12px;fill:#666}
.axisX text{text-anchor:middle}
.xAngle text{text-anchor:start}
.axisY text{text-anchor:end}
.chart circle{stroke-width:2px;stroke:#eee}
.chart .line{fill:none;stroke-width:3}
.chart .fill{stroke-width:0}
.valShow text,.pie text{fill:#fed;opacity:.8;font-size:10px}
.pie text{font-size:15px}
.chartInfo g{opacity:0}
.chartInfo g:hover{opacity:1}
.chartInfo .textBg{fill:#000;opacity:.9}
.chartInfo text{text-anchor:middle;fill:#887}
.chartInfo g rect:nth-child(even){fill:#eee;opacity:.8;stroke:#eed;stroke-width:1}
.pieBg{fill:#eee}
]]>
';
    if ($style) echo $style . "\n";
    echo '</style>' . "\n";
}
