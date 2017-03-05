<?php
require_once 'vendor/autoload.php';

function randomData($count = 96, $offsetMax = 2000) {
  $randomData = [];
  $duration = 60 * 5 + rand() * 60 * 60 * 24;
  $begin = time() - $duration;
  $offset = $offsetMax * (rand()/getRandMax())**2;
  $scale = max(0.25 * $offset, 100 * rand() / getRandMax());
  $volatility = 0.5 * (rand()/getRandMax())**3 + 0.25;
  for ($n = 0, $current = $offset + 0.5 * $scale; $n < $count; $n++) {
    $current -= $offset;
    $current *= 1 + $volatility * (rand()/getRandMax() - 0.5);
    $current += $offset;
    $randomData[$begin + $duration / $count * $n] = $current;
  }
  return $randomData;
}

?>
<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0">
  <title>Demo Page : seigler/neat-charts</title>
  <style>
    *, *:after, *:before { box-sizing: inherit; }
  </style>
</head>
<body>
  <header>
    <h1>neat-charts examples</h1>
  </header>
  <main>
    <section>
      <h2>Line chart in <code>img</code> tag</h2>
      <figure>
        <img src="./demo-as-image.php">
        <figcaption>Random generated data, loaded as an image</figcaption>
      </figure>
    </section>
    <section>
      <h2>Line chart in <code>svg</code> tag, zero axis shown, filled, smoothed</h2>
      <figure>
<?php
$chart = new NeatCharts\LineChart(randomData(), [
  'width'=>800,
  'height'=>250,
  'lineColor'=>'#F00',
  'labelColor'=>'#222',
  'smoothed'=>true,
  'fontSize'=>14,
  'yAxisZero'=>true,
  'filled'=>true
]);
echo $chart->render();
?>
        <figcaption>Random generated data</figcaption>
      </figure>
    </section>
    <section>
      <h2>Smoothed line chart in <code>svg</code> tag</h2>
      <figure>
<?php
$chart = new NeatCharts\LineChart(randomData(12), [
  'width'=>400,
  'height'=>300, // null works as a height too, it picks a height that makes the plot look good
  'lineColor'=>'#080',
  'labelColor'=>'#222',
  'smoothed'=>true,
  'fontSize'=>14
]);
echo $chart->render();
?>
        <figcaption>Random generated data</figcaption>
      </figure>
    </section>
    <section>
      <h2>Sparkline in <code>svg</code> tag</h2>
      <figure>
        <?php
$chart = new NeatCharts\LineChart(randomData(48), [
  'width'=>100,
  'height'=>20,
  'lineColor'=>'#000',
  'markerColor'=>'#F00',
  'smoothed'=>false,
  'fontSize'=>4,
  'yAxisEnabled'=>false,
  'xAxisEnabled'=>false
]);
echo $chart->render();
?>
        <figcaption>Random generated data</figcaption>
      </figure>
    </section>
    <section>
      <h2>Bar chart in <code>svg</code> tag</h2>
      <figure>
<?php
        $chart = new NeatCharts\BarChart(randomData(10, 0), [
          'barColor'=>'#409'
        ]);
        echo $chart->render();
?>
        <figcaption>Random generated data</figcaption>
      </figure>
    </section>
    <section>
      <h2>Candlestick chart in <code>svg</code> tag, zero axis shown, filled</h2>
      <figure>
<?php
$poloData = json_decode('[{"date":1466265600,"high":0.01132458,"low":0.0105,"open":0.01125734,"close":0.01063397,"volume":65.66865359,"quoteVolume":6113.28955617,"weightedAverage":0.01074195},{"date":1466280000,"high":0.01094877,"low":0.01052,"open":0.01053829,"close":0.01077103,"volume":18.27043901,"quoteVolume":1696.74470047,"weightedAverage":0.01076793},{"date":1466294400,"high":0.01132121,"low":0.01057856,"open":0.0107702,"close":0.01100007,"volume":92.69494189,"quoteVolume":8443.55853321,"weightedAverage":0.01097818},{"date":1466308800,"high":0.01121353,"low":0.01056001,"open":0.011,"close":0.0106777,"volume":53.41416524,"quoteVolume":4933.24263047,"weightedAverage":0.01082739},{"date":1466323200,"high":0.01087,"low":0.01055672,"open":0.01083678,"close":0.01078095,"volume":33.44517732,"quoteVolume":3124.05684323,"weightedAverage":0.01070568},{"date":1466337600,"high":0.01097187,"low":0.0106211,"open":0.01082827,"close":0.01092986,"volume":27.23802069,"quoteVolume":2516.80897654,"weightedAverage":0.01082244},{"date":1466352000,"high":0.01092918,"low":0.010531,"open":0.01091441,"close":0.01061411,"volume":42.25071052,"quoteVolume":3947.49757135,"weightedAverage":0.01070316},{"date":1466366400,"high":0.01067675,"low":0.01036112,"open":0.01057819,"close":0.01051729,"volume":38.85739743,"quoteVolume":3708.46940188,"weightedAverage":0.01047801},{"date":1466380800,"high":0.01075615,"low":0.01045106,"open":0.01049381,"close":0.01059708,"volume":21.05733964,"quoteVolume":1984.21730692,"weightedAverage":0.01061241},{"date":1466395200,"high":0.01067353,"low":0.01043621,"open":0.01059479,"close":0.01052711,"volume":31.94467592,"quoteVolume":3026.92690315,"weightedAverage":0.0105535},{"date":1466409600,"high":0.01066373,"low":0.01041,"open":0.01052711,"close":0.01057445,"volume":46.5201359,"quoteVolume":4420.84615223,"weightedAverage":0.0105229},{"date":1466424000,"high":0.0113,"low":0.01045888,"open":0.01050181,"close":0.01103395,"volume":97.35819073,"quoteVolume":8876.23539131,"weightedAverage":0.01096841},{"date":1466438400,"high":0.0111252,"low":0.01068002,"open":0.01109875,"close":0.01107757,"volume":52.60381188,"quoteVolume":4815.23202078,"weightedAverage":0.01092446},{"date":1466452800,"high":0.01145,"low":0.01085685,"open":0.01109875,"close":0.01128182,"volume":72.57345956,"quoteVolume":6522.6208191,"weightedAverage":0.01112642},{"date":1466467200,"high":0.01233028,"low":0.01102039,"open":0.0112,"close":0.01152974,"volume":168.52451606,"quoteVolume":14366.61665034,"weightedAverage":0.01173028},{"date":1466481600,"high":0.01171231,"low":0.01133644,"open":0.01152974,"close":0.01153969,"volume":50.18773261,"quoteVolume":4353.64129199,"weightedAverage":0.01152776},{"date":1466496000,"high":0.01163279,"low":0.01131257,"open":0.01153942,"close":0.01157066,"volume":48.07279293,"quoteVolume":4187.81260825,"weightedAverage":0.01147921},{"date":1466510400,"high":0.01210116,"low":0.0115,"open":0.01162843,"close":0.01198993,"volume":75.69146736,"quoteVolume":6436.62105311,"weightedAverage":0.0117595},{"date":1466524800,"high":0.01206122,"low":0.01168409,"open":0.0119,"close":0.01189526,"volume":48.43242576,"quoteVolume":4061.82458093,"weightedAverage":0.01192381},{"date":1466539200,"high":0.01198989,"low":0.01147144,"open":0.01186793,"close":0.01161501,"volume":69.2989149,"quoteVolume":5904.30986797,"weightedAverage":0.011737},{"date":1466553600,"high":0.01211972,"low":0.0113,"open":0.01157611,"close":0.01207401,"volume":82.98873945,"quoteVolume":7121.58550764,"weightedAverage":0.01165312},{"date":1466568000,"high":0.0121555,"low":0.0114,"open":0.01207401,"close":0.01170818,"volume":57.43825631,"quoteVolume":4866.45709584,"weightedAverage":0.01180288},{"date":1466582400,"high":0.01199999,"low":0.01161,"open":0.0117215,"close":0.0117082,"volume":48.38594383,"quoteVolume":4112.37440937,"weightedAverage":0.01176593},{"date":1466596800,"high":0.01195826,"low":0.0117075,"open":0.01170819,"close":0.0119407,"volume":49.98327652,"quoteVolume":4224.4416365,"weightedAverage":0.01183192},{"date":1466611200,"high":0.01240744,"low":0.01190001,"open":0.01190001,"close":0.0122708,"volume":157.77492573,"quoteVolume":12977.1344226,"weightedAverage":0.01215791},{"date":1466625600,"high":0.01260571,"low":0.01213152,"open":0.01214002,"close":0.01249568,"volume":97.46051421,"quoteVolume":7852.70778788,"weightedAverage":0.01241107},{"date":1466640000,"high":0.0128,"low":0.01212222,"open":0.01237699,"close":0.01235569,"volume":99.59279746,"quoteVolume":7970.03781156,"weightedAverage":0.0124959},{"date":1466654400,"high":0.01283311,"low":0.01225135,"open":0.01235551,"close":0.01247264,"volume":52.91167734,"quoteVolume":4189.23380099,"weightedAverage":0.01263039},{"date":1466668800,"high":0.01255755,"low":0.01226645,"open":0.01247264,"close":0.01231813,"volume":33.77873684,"quoteVolume":2727.8670538,"weightedAverage":0.01238283},{"date":1466683200,"high":0.0124668,"low":0.01217062,"open":0.01236905,"close":0.01226,"volume":47.34535123,"quoteVolume":3856.74405268,"weightedAverage":0.01227598},{"date":1466697600,"high":0.01236085,"low":0.01161445,"open":0.01225999,"close":0.01193703,"volume":124.89925227,"quoteVolume":10458.88074055,"weightedAverage":0.01194193},{"date":1466712000,"high":0.01193703,"low":0.01149691,"open":0.01193702,"close":0.01159169,"volume":52.58479905,"quoteVolume":4518.34260631,"weightedAverage":0.01163807},{"date":1466726400,"high":0.01185006,"low":0.01127,"open":0.01159116,"close":0.01127007,"volume":74.06357707,"quoteVolume":6404.69592514,"weightedAverage":0.01156394},{"date":1466740800,"high":0.01130863,"low":0.01029,"open":0.01127001,"close":0.01107041,"volume":143.7739451,"quoteVolume":13344.55111254,"weightedAverage":0.01077398},{"date":1466755200,"high":0.01110831,"low":0.01058098,"open":0.01107066,"close":0.01087142,"volume":34.94836986,"quoteVolume":3213.94630626,"weightedAverage":0.01087397},{"date":1466769600,"high":0.01125778,"low":0.010711,"open":0.01087132,"close":0.01124473,"volume":56.41317728,"quoteVolume":5132.91704766,"weightedAverage":0.01099047},{"date":1466784000,"high":0.0114,"low":0.01041354,"open":0.01125777,"close":0.01059998,"volume":115.16310544,"quoteVolume":10735.6429396,"weightedAverage":0.01072717},{"date":1466798400,"high":0.01075744,"low":0.0103,"open":0.01062467,"close":0.01069989,"volume":47.7625585,"quoteVolume":4524.17292748,"weightedAverage":0.01055719},{"date":1466812800,"high":0.01080255,"low":0.00945003,"open":0.01069989,"close":0.01010798,"volume":185.30789188,"quoteVolume":18527.99727028,"weightedAverage":0.0100015},{"date":1466827200,"high":0.01023041,"low":0.01,"open":0.01000915,"close":0.01012612,"volume":44.12156526,"quoteVolume":4367.55841838,"weightedAverage":0.01010211},{"date":1466841600,"high":0.010498,"low":0.00980105,"open":0.01013033,"close":0.01048098,"volume":72.85347323,"quoteVolume":7255.85257563,"weightedAverage":0.01004064},{"date":1466856000,"high":0.01048094,"low":0.0101,"open":0.01040791,"close":0.01045,"volume":50.91074532,"quoteVolume":4943.39549602,"weightedAverage":0.01029874},{"date":1466870400,"high":0.01050411,"low":0.01020969,"open":0.01044999,"close":0.01035,"volume":42.3855895,"quoteVolume":4089.63553095,"weightedAverage":0.01036414},{"date":1466884800,"high":0.01037911,"low":0.01020004,"open":0.01035557,"close":0.01032614,"volume":20.8109567,"quoteVolume":2019.62619976,"weightedAverage":0.01030436},{"date":1466899200,"high":0.01052953,"low":0.01029971,"open":0.01032614,"close":0.01052899,"volume":15.40546239,"quoteVolume":1482.96391235,"weightedAverage":0.01038829},{"date":1466913600,"high":0.01099376,"low":0.01045677,"open":0.01048249,"close":0.0107837,"volume":34.44107409,"quoteVolume":3187.66441392,"weightedAverage":0.01080448},{"date":1466928000,"high":0.01099435,"low":0.01063968,"open":0.0107837,"close":0.01076187,"volume":41.23583397,"quoteVolume":3819.59901691,"weightedAverage":0.01079585},{"date":1466942400,"high":0.01106337,"low":0.01072602,"open":0.01079778,"close":0.01090378,"volume":42.72578839,"quoteVolume":3913.30018602,"weightedAverage":0.01091809},{"date":1466956800,"high":0.01106615,"low":0.010891,"open":0.01093559,"close":0.01092675,"volume":27.95715137,"quoteVolume":2545.20739446,"weightedAverage":0.01098423},{"date":1466971200,"high":0.01101562,"low":0.01063119,"open":0.01092675,"close":0.01079217,"volume":43.96205737,"quoteVolume":4079.74026174,"weightedAverage":0.0107757},{"date":1466985600,"high":0.01081104,"low":0.01058,"open":0.01080534,"close":0.01061366,"volume":25.85667327,"quoteVolume":2423.20128759,"weightedAverage":0.01067046},{"date":1467000000,"high":0.01063856,"low":0.01057352,"open":0.01058,"close":0.01063763,"volume":12.99378748,"quoteVolume":1224.97552892,"weightedAverage":0.01060738},{"date":1467014400,"high":0.01070119,"low":0.01038,"open":0.01058001,"close":0.01065001,"volume":42.54818632,"quoteVolume":4042.28510174,"weightedAverage":0.01052577},{"date":1467028800,"high":0.01084579,"low":0.01046753,"open":0.01070355,"close":0.01084579,"volume":49.73388496,"quoteVolume":4659.29800073,"weightedAverage":0.01067411},{"date":1467043200,"high":0.01090097,"low":0.01060241,"open":0.01084575,"close":0.01079988,"volume":11.51574819,"quoteVolume":1072.4258471,"weightedAverage":0.01073803},{"date":1467057600,"high":0.01093856,"low":0.01060011,"open":0.01079965,"close":0.01080806,"volume":32.90745161,"quoteVolume":3040.08182862,"weightedAverage":0.01082452},{"date":1467072000,"high":0.01090032,"low":0.01061642,"open":0.01080806,"close":0.01062484,"volume":31.9220025,"quoteVolume":2981.89630252,"weightedAverage":0.01070526},{"date":1467086400,"high":0.0107607,"low":0.01058151,"open":0.01062484,"close":0.01070584,"volume":14.88109157,"quoteVolume":1394.33343436,"weightedAverage":0.01067254},{"date":1467100800,"high":0.01082748,"low":0.01064693,"open":0.0107,"close":0.01082748,"volume":15.37355215,"quoteVolume":1428.91471173,"weightedAverage":0.0107589},{"date":1467115200,"high":0.011066,"low":0.01080897,"open":0.01080897,"close":0.0110659,"volume":23.87130076,"quoteVolume":2186.81595299,"weightedAverage":0.010916},{"date":1467129600,"high":0.01106615,"low":0.0108304,"open":0.0110659,"close":0.01099979,"volume":24.01515069,"quoteVolume":2186.80179301,"weightedAverage":0.01098185},{"date":1467144000,"high":0.010999,"low":0.01052603,"open":0.010999,"close":0.010703,"volume":69.49665067,"quoteVolume":6477.1320393,"weightedAverage":0.01072954},{"date":1467158400,"high":0.01104684,"low":0.01053265,"open":0.01060324,"close":0.01082496,"volume":22.18112,"quoteVolume":2045.33926944,"weightedAverage":0.01084471},{"date":1467172800,"high":0.01106347,"low":0.01074224,"open":0.01082112,"close":0.01102577,"volume":12.90342145,"quoteVolume":1174.54333551,"weightedAverage":0.0109859},{"date":1467187200,"high":0.01110998,"low":0.01080001,"open":0.01102573,"close":0.0110003,"volume":26.10999652,"quoteVolume":2371.25090092,"weightedAverage":0.01101106},{"date":1467201600,"high":0.0111241,"low":0.01089214,"open":0.01100092,"close":0.01098811,"volume":42.13913218,"quoteVolume":3815.86222398,"weightedAverage":0.01104314},{"date":1467216000,"high":0.01108367,"low":0.01085385,"open":0.01105207,"close":0.01086721,"volume":22.85211545,"quoteVolume":2092.07696404,"weightedAverage":0.01092317},{"date":1467230400,"high":0.01104532,"low":0.01082836,"open":0.01086721,"close":0.0109311,"volume":16.25552664,"quoteVolume":1490.07143677,"weightedAverage":0.01090922},{"date":1467244800,"high":0.0111241,"low":0.0108,"open":0.0109311,"close":0.0109559,"volume":45.6721326,"quoteVolume":4155.09541382,"weightedAverage":0.01099183},{"date":1467259200,"high":0.01127764,"low":0.01091824,"open":0.01095589,"close":0.01116615,"volume":25.55878441,"quoteVolume":2303.06409719,"weightedAverage":0.01109773},{"date":1467273600,"high":0.01128118,"low":0.01084126,"open":0.01116784,"close":0.01099797,"volume":40.16102625,"quoteVolume":3624.28868129,"weightedAverage":0.01108107},{"date":1467288000,"high":0.0111263,"low":0.0105,"open":0.01099798,"close":0.01069074,"volume":79.80968639,"quoteVolume":7479.95760209,"weightedAverage":0.0106698},{"date":1467302400,"high":0.01087791,"low":0.01045001,"open":0.01069074,"close":0.01076153,"volume":36.64652164,"quoteVolume":3446.0254455,"weightedAverage":0.01063443},{"date":1467316800,"high":0.01078688,"low":0.01045366,"open":0.01076153,"close":0.01061479,"volume":12.25397547,"quoteVolume":1153.43835193,"weightedAverage":0.01062386},{"date":1467331200,"high":0.01090999,"low":0.01055975,"open":0.01061084,"close":0.01062535,"volume":9.81679733,"quoteVolume":917.91272455,"weightedAverage":0.01069469},{"date":1467345600,"high":0.01089083,"low":0.01060666,"open":0.01062535,"close":0.01081997,"volume":21.45982772,"quoteVolume":1997.09829298,"weightedAverage":0.0107455},{"date":1467360000,"high":0.01097232,"low":0.0106,"open":0.010835,"close":0.01062639,"volume":22.42442321,"quoteVolume":2094.15153884,"weightedAverage":0.01070811},{"date":1467374400,"high":0.01091986,"low":0.010625,"open":0.01062639,"close":0.01066999,"volume":31.64946909,"quoteVolume":2938.12927762,"weightedAverage":0.01077197},{"date":1467388800,"high":0.01079443,"low":0.01061642,"open":0.01066999,"close":0.01075774,"volume":16.73164207,"quoteVolume":1559.73839436,"weightedAverage":0.01072721},{"date":1467403200,"high":0.01077599,"low":0.01063481,"open":0.01069876,"close":0.0107221,"volume":12.29154864,"quoteVolume":1152.25787779,"weightedAverage":0.01066735},{"date":1467417600,"high":0.01072339,"low":0.01052,"open":0.0107221,"close":0.01059171,"volume":12.59742204,"quoteVolume":1186.49305946,"weightedAverage":0.01061735},{"date":1467432000,"high":0.01066939,"low":0.01056,"open":0.01059172,"close":0.01060903,"volume":5.39516871,"quoteVolume":508.42302903,"weightedAverage":0.01061157},{"date":1467446400,"high":0.01067582,"low":0.0105001,"open":0.01066999,"close":0.0105598,"volume":19.14939844,"quoteVolume":1815.85482061,"weightedAverage":0.01054566},{"date":1467460800,"high":0.01074706,"low":0.01052511,"open":0.0105598,"close":0.0107,"volume":23.4265639,"quoteVolume":2205.4031608,"weightedAverage":0.01062234},{"date":1467475200,"high":0.0107,"low":0.01,"open":0.0107,"close":0.01047884,"volume":104.70943165,"quoteVolume":10159.31937238,"weightedAverage":0.01030673},{"date":1467489600,"high":0.0105591,"low":0.01005007,"open":0.01040601,"close":0.01021759,"volume":37.16492985,"quoteVolume":3649.40216188,"weightedAverage":0.01018384},{"date":1467504000,"high":0.01040601,"low":0.01020003,"open":0.01021759,"close":0.01033344,"volume":10.13682687,"quoteVolume":983.01946578,"weightedAverage":0.01031192},{"date":1467518400,"high":0.01061898,"low":0.01033264,"open":0.01040776,"close":0.0105502,"volume":33.82592606,"quoteVolume":3218.36124332,"weightedAverage":0.01051029},{"date":1467532800,"high":0.010776,"low":0.0105,"open":0.0105502,"close":0.01060631,"volume":34.02283338,"quoteVolume":3202.8521008,"weightedAverage":0.01062266},{"date":1467547200,"high":0.01076387,"low":0.01050005,"open":0.01060631,"close":0.01071005,"volume":29.96744141,"quoteVolume":2816.46866954,"weightedAverage":0.01064007},{"date":1467561600,"high":0.01076386,"low":0.01051996,"open":0.01068591,"close":0.01066885,"volume":19.43315944,"quoteVolume":1828.55425063,"weightedAverage":0.0106276},{"date":1467576000,"high":0.01069543,"low":0.0105,"open":0.01066883,"close":0.0106286,"volume":47.26864204,"quoteVolume":4461.19178955,"weightedAverage":0.01059551},{"date":1467590400,"high":0.01083096,"low":0.01055708,"open":0.0106265,"close":0.01069483,"volume":16.87774817,"quoteVolume":1575.61582471,"weightedAverage":0.01071184},{"date":1467604800,"high":0.01086028,"low":0.01056504,"open":0.01066201,"close":0.01056714,"volume":9.48739355,"quoteVolume":888.44539649,"weightedAverage":0.01067864},{"date":1467619200,"high":0.01070188,"low":0.010563,"open":0.01062197,"close":0.01064873,"volume":9.80335201,"quoteVolume":920.21125555,"weightedAverage":0.01065337},{"date":1467633600,"high":0.01064933,"low":0.0105,"open":0.01062721,"close":0.01050009,"volume":7.4286323,"quoteVolume":702.89122431,"weightedAverage":0.01056867},{"date":1467648000,"high":0.01070094,"low":0.01047242,"open":0.01051558,"close":0.01049392,"volume":10.75687847,"quoteVolume":1017.30259219,"weightedAverage":0.01057392},{"date":1467662400,"high":0.01049392,"low":0.01001009,"open":0.01049385,"close":0.01045048,"volume":36.60237491,"quoteVolume":3577.32019968,"weightedAverage":0.01023178},{"date":1467676800,"high":0.01045053,"low":0.01023,"open":0.01045053,"close":0.01025899,"volume":9.36173661,"quoteVolume":909.24322697,"weightedAverage":0.01029618},{"date":1467691200,"high":0.01043226,"low":0.01017752,"open":0.01023,"close":0.0102332,"volume":21.71048738,"quoteVolume":2124.69928614,"weightedAverage":0.01021814},{"date":1467705600,"high":0.01032712,"low":0.01017675,"open":0.01023266,"close":0.01022631,"volume":19.73601455,"quoteVolume":1933.24990515,"weightedAverage":0.01020872},{"date":1467720000,"high":0.01045,"low":0.01015959,"open":0.01021756,"close":0.01041208,"volume":25.80192931,"quoteVolume":2510.92165436,"weightedAverage":0.01027587},{"date":1467734400,"high":0.010475,"low":0.01021275,"open":0.01033297,"close":0.01027514,"volume":17.78563101,"quoteVolume":1719.69851266,"weightedAverage":0.01034229},{"date":1467748800,"high":0.01046999,"low":0.0102127,"open":0.01027513,"close":0.01043729,"volume":21.41132582,"quoteVolume":2078.46827,"weightedAverage":0.01030149},{"date":1467763200,"high":0.01049481,"low":0.01019569,"open":0.01037469,"close":0.01039998,"volume":26.87237023,"quoteVolume":2604.60972495,"weightedAverage":0.01031723},{"date":1467777600,"high":0.01055,"low":0.01024024,"open":0.01035002,"close":0.01035,"volume":26.25450521,"quoteVolume":2525.75975113,"weightedAverage":0.01039469},{"date":1467792000,"high":0.01064317,"low":0.01025,"open":0.01035,"close":0.01039999,"volume":30.11732386,"quoteVolume":2881.5066508,"weightedAverage":0.01045193},{"date":1467806400,"high":0.01044469,"low":0.01025314,"open":0.01036117,"close":0.01040253,"volume":17.8267321,"quoteVolume":1722.90657115,"weightedAverage":0.01034689},{"date":1467820800,"high":0.01060944,"low":0.01032877,"open":0.01035883,"close":0.01039776,"volume":9.45316009,"quoteVolume":902.33222795,"weightedAverage":0.01047636},{"date":1467835200,"high":0.0104,"low":0.0103,"open":0.01039776,"close":0.01035095,"volume":7.86671024,"quoteVolume":762.21791658,"weightedAverage":0.01032081},{"date":1467849600,"high":0.010456,"low":0.01028,"open":0.0103,"close":0.01044725,"volume":20.63038607,"quoteVolume":1992.34253642,"weightedAverage":0.01035483},{"date":1467864000,"high":0.01103333,"low":0.01040423,"open":0.01040423,"close":0.01092237,"volume":70.24505094,"quoteVolume":6508.50405041,"weightedAverage":0.01079281}]');

$chart = new NeatCharts\CandlestickChart($poloData, [
]);
echo $chart->render();
?>
        <figcaption>Random generated data</figcaption>
      </figure>
    </section>
  </main>
</body>
</html>
