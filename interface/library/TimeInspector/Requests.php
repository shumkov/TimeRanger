<?php

class TimeInspector_Requests
{
	public static function average($requests)
	{
        $averageData = array();
        $metrics = array();

        $requestsCount = count($requests);

        foreach($requests as $index => $request) {
            $data = $request->data['profile'];

            if ($index == 0) {
                foreach ($data['main()'] as $metric => $val) {
                    if ($metric != 'pmu') {
                        if (isset($val)) {
                            $metrics[] = $metric;
                        }
                    }
                }
            }

            foreach ($data as $parentChild => $info) {
                if (!isset($averageData[$parentChild])) {
                    $averageData[$parentChild] = array();
                    foreach ($metrics as $metric) {
                        $averageData[$parentChild][$metric] = $info[$metric];
                    }
                } else {
                    foreach ($metrics as $metric) {
                        $averageData[$parentChild][$metric] += $info[$metric];
                    }
                }
            }
        }

        foreach ($averageData as $parentChild => &$info) {
            foreach ($info as $metric => &$value) {
                $value = ($value / $requestsCount);
            }
        }

        return $averageData;
	}
}