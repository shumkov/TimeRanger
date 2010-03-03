<?php

class TimeInspector_View_Helper_Table
{
    protected static $_metrics = array("wt" => array("Wall" , "microsecs" , "walltime") , "ut" => array("User" , "microsecs" , "user cpu time") , "st" => array("Sys" , "microsecs" , "system cpu time") , "cpu" => array("Cpu" , "microsecs" , "cpu time") , "mu" => array("MUse" , "bytes" , "memory usage") , "pmu" => array("PMUse" , "bytes" , "peak memory usage") , "samples" => array("Samples" , "samples" , "cpu time"));
    protected static $_sortableColumns = array("fn" => 1 , "ct" => 1 , "wt" => 1 , "excl_wt" => 1 , "ut" => 1 , "excl_ut" => 1 , "st" => 1 , "excl_st" => 1 , "mu" => 1 , "excl_mu" => 1 , "pmu" => 1 , "excl_pmu" => 1 , "cpu" => 1 , "excl_cpu" => 1 , "samples" => 1 , "excl_samples" => 1);
    
    public function table($data)
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $sort = $request->getParam('sort');
        if (!array_key_exists($sort, self::$_sortableColumns)) {
            $sort = null;
        }
        $view = $request->getParam('view');
        $view = 'flat';

        $stats = array("fn" , "ct" , "Calls%");
        $pc_stats = $stats;

        $metrics = array();
        foreach (self::$_metrics as $metric => $desc) {
            if (isset($data["main()"][$metric])) {
                $metrics[] = $metric;
                // flat (top-level reports): we can compute
                // exclusive metrics reports as well.
                $stats[] = $metric;
                $stats[] = "I" . $desc[0] . "%";
                $stats[] = "excl_" . $metric;
                $stats[] = "E" . $desc[0] . "%";
                // parent/child report for a function: we can
                // only breakdown inclusive times correctly.
                $pc_stats[] = $metric;
                $pc_stats[] = "I" . $desc[0] . "%";
            }
        }

    $symbol_tab = xhprof_compute_flat_info($run1_data, $totals);

  $run1_txt = sprintf("<b>Run #%s:</b> %s",
                      $run1, $run1_desc);

  $base_url_params = xhprof_array_unset(xhprof_array_unset($url_params,
                                                           'symbol'),
                                        'all');

  $top_link_query_string = "$base_path/?" . http_build_query($base_url_params);

  if ($diff_mode) {
    $diff_text = "Diff";
    $base_url_params = xhprof_array_unset($base_url_params, 'run1');
    $base_url_params = xhprof_array_unset($base_url_params, 'run2');
    $run1_link = xhprof_render_link('View Run #' . $run1,
                           "$base_path/?" .
                           http_build_query(xhprof_array_set($base_url_params,
                                                      'run',
                                                      $run1)));
    $run2_txt = sprintf("<b>Run #%s:</b> %s",
                        $run2, $run2_desc);

    $run2_link = xhprof_render_link('View Run #' . $run2,
                                    "$base_path/?" .
                        http_build_query(xhprof_array_set($base_url_params,
                                                          'run',
                                                          $run2)));
  } else {
    $diff_text = "Run";
  }

  // set up the action links for operations that can be done on this report
  $links = array();
  $links []=  xhprof_render_link("View Top Level $diff_text Report",
                                 $top_link_query_string);

  if ($diff_mode) {
    $inverted_params = $url_params;
    $inverted_params['run1'] = $url_params['run2'];
    $inverted_params['run2'] = $url_params['run1'];

    // view the different runs or invert the current diff
    $links []= $run1_link;
    $links []= $run2_link;
    $links []= xhprof_render_link('Invert ' . $diff_text . ' Report',
                           "$base_path/?".
                           http_build_query($inverted_params));
  }

  // lookup function typeahead form
  $links [] = '<input class="function_typeahead" ' .
              ' type="input" size="40" maxlength="100" />';

  echo xhprof_render_actions($links);


  echo
    '<dl class=phprof_report_info>' .
    '  <dt>' . $diff_text . ' Report</dt>' .
    '  <dd>' . ($diff_mode ?
                $run1_txt . '<br><b>vs.</b><br>' . $run2_txt :
                $run1_txt) .
    '  </dd>' .
    '  <dt>Tip</dt>' .
    '  <dd>Click a function name below to drill down.</dd>' .
    '</dl>' .
    '<div style="clear: both; margin: 3em 0em;"></div>';
                
                if ($diff_mode) {

    $base_url_params = xhprof_array_unset(xhprof_array_unset($url_params,
                                                             'run1'),
                                          'run2');
    $href1 = "$base_path/?" .
      http_build_query(xhprof_array_set($base_url_params,
                                        'run', $run1));
    $href2 = "$base_path/?" .
      http_build_query(xhprof_array_set($base_url_params,
                                        'run', $run2));

    print("<h3><center>Overall Diff Summary</center></h3>");
    print('<Table border=1 cellpadding=2 cellspacing=1 width="30%" '
          .'rules=rows bordercolor="#bdc7d8" align=center>' . "\n");
    print('<tr bgcolor="#bdc7d8" align=right>');
    print("<th></th>");
    print("<th $vwbar>" . xhprof_render_link("Run #$run1", $href1) . "</th>");
    print("<th $vwbar>" . xhprof_render_link("Run #$run2", $href2) . "</th>");
    print("<th $vwbar>Diff</th>");
    print("<th $vwbar>Diff%</th>");
    print('</tr>');

    if ($display_calls) {
      print('<tr>');
      print("<td>Number of Function Calls</td>");
      print_td_num($totals_1["ct"], $format_cbk["ct"]);
      print_td_num($totals_2["ct"], $format_cbk["ct"]);
      print_td_num($totals_2["ct"] - $totals_1["ct"], $format_cbk["ct"], true);
      print_td_pct($totals_2["ct"] - $totals_1["ct"], $totals_1["ct"], true);
      print('</tr>');
    }

    foreach ($metrics as $metric) {
      $m = $metric;
      print('<tr>');
      print("<td>" . str_replace("<br>", " ", $descriptions[$m]) . "</td>");
      print_td_num($totals_1[$m], $format_cbk[$m]);
      print_td_num($totals_2[$m], $format_cbk[$m]);
      print_td_num($totals_2[$m] - $totals_1[$m], $format_cbk[$m], true);
      print_td_pct($totals_2[$m] - $totals_1[$m], $totals_1[$m], true);
      print('<tr>');
    }
    print('</Table>');

    $callgraph_report_title = '[View Regressions/Improvements using Callgraph Diff]';

  } else {
    print("<p><center>\n");

    print('<Table cellpadding=2 cellspacing=1 width="30%" '
          .'bgcolor="#bdc7d8" align=center>' . "\n");
    echo "<tr>";
    echo "<th style='text-align:right'>Overall Summary</th>";
    echo "<th'></th>";
    echo "</tr>";

    foreach ($metrics as $metric) {
      echo "<tr>";
      echo "<td style='text-align:right; font-weight:bold'>Total "
            . str_replace("<br>", " ", stat_description($metric)) . ":</td>";
      echo "<td>" . number_format($totals[$metric]) .  " "
           . $possible_metrics[$metric][1] . "</td>";
      echo "</tr>";
    }

    if ($display_calls) {
      echo "<tr>";
      echo "<td style='text-align:right; font-weight:bold'>Number of Function Calls:</td>";
      echo "<td>" . number_format($totals['ct']) . "</td>";
      echo "</tr>";
    }

    echo "</Table>";
    print("</center></p>\n");

    $callgraph_report_title = '[View Full Callgraph]';
  }

  print("<center><br><h3>" .
        xhprof_render_link($callgraph_report_title,
                    "$base_path/callgraph.php" . "?" . http_build_query($url_params))
        . "</h3></center>");


  $flat_data = array();
  foreach ($symbol_tab as $symbol => $info) {
    $tmp = $info;
    $tmp["fn"] = $symbol;
    $flat_data[] = $tmp;
  }
  usort($flat_data, 'sort_cbk');

  print("<br>");

  if (!empty($url_params['all'])) {
    $all = true;
    $limit = 0;    // display all rows
  } else {
    $all = false;
    $limit = 100;  // display only limited number of rows
  }

  $desc = str_replace("<br>", " ", $descriptions[$sort_col]);

  if ($diff_mode) {
    if ($all) {
      $title = "Total Diff Report: '
               .'Sorted by absolute value of regression/improvement in $desc";
    } else {
      $title = "Top 100 <i style='color:red'>Regressions</i>/"
               . "<i style='color:green'>Improvements</i>: "
               . "Sorted by $desc Diff";
    }
  } else {
    if ($all) {
      $title = "Sorted by $desc";
    } else {
      $title = "Displaying top $limit functions: Sorted by $desc";
    }
  }
  print_flat_data($url_params, $title, $flat_data, $sort, $run1, $run2, $limit);
    }
}