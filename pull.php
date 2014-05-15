<?php
if ( $argv && count($argv) > 1 ) {
	$days = $argv[1];
} else {
	$days = 0;
}
function escapeKibanaQuery( $query ) {
  return str_replace( '"', '\"', $query );
}

$query_template = <<<EOD
{
  "facets": {
    "0": {
      "date_histogram": {
        "field": "@timestamp",
        "interval": "24h"
      },
      "global": true,
      "facet_filter": {
        "fquery": {
          "query": {
            "filtered": {
              "query": {
                "query_string": {
                  "query": "__query__"
                }
              }
            }
          }
        }
      }
    }
  },
  "size": 0
}
EOD;

function ratio( $small, $big ) {
  return round( $small / $big * 100 ) . '%';
}

function runQueries( $dateString, $queries ) {
  global $query_template;
  $results = array();
  foreach ( $queries as $key => $val ) {
    $query = $query_template;
    $query = str_replace( '__query__', escapeKibanaQuery( $val ), $query );
    $command = "curl -s -XGET http://datalog-s3:9200/logstash-{$dateString}/_search?pretty -d '{$query}'";
echo $command."\n\n\n";
    $output = shell_exec ( $command );
print_r($output);
    $outputO = json_decode( $output, true );
    $results[$key] = $outputO['facets']['0']['entries']['0']['count'];
    if ( empty ( $results[$key] ) ) {
      $results[$key] = 0;
    }
  }
  return $results;
}


$queries = array();
$queries['ck imp isAnonymous=yes isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ck-edit-page-start" AND @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ck imp isAnonymous=yes isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ck-edit-page-start" AND @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';
$queries['ck imp isAnonymous=no isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ck-edit-page-start" NOT @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ck imp isAnonymous=no isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ck-edit-page-start" NOT @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';

$queries['ve impstart isAnonymous=yes isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-start" AND @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ve impstart isAnonymous=yes isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-start" AND @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';
$queries['ve impstart isAnonymous=no isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-start" NOT @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ve impstart isAnonymous=no isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-start" NOT @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';

$queries['ve impstop isAnonymous=yes isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-stop" AND @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ve impstop isAnonymous=yes isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-stop" AND @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';
$queries['ve impstop isAnonymous=no isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-stop" NOT @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ve impstop isAnonymous=no isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ve-edit-page-stop" NOT @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';

$queries['ck save isAnonymous=yes isRedlink=yes isDirty=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" AND @context.isAnonymous:"yes" AND @context.isRedlink:"yes" AND @context.isDirty:"yes"';
$queries['ck save isAnonymous=yes isRedlink=no isDirty=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" AND @context.isAnonymous:"yes" NOT @context.isRedlink:"yes" AND @context.isDirty:"yes"';
$queries['ck save isAnonymous=no isRedlink=yes isDirty=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" NOT @context.isAnonymous:"yes" AND @context.isRedlink:"yes" AND @context.isDirty:"yes"';
$queries['ck save isAnonymous=no isRedlink=no isDirty=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" NOT @context.isAnonymous:"yes" NOT @context.isRedlink:"yes" AND @context.isDirty:"yes"';
$queries['ck save isAnonymous=yes isRedlink=yes isDirty=no'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" AND @context.isAnonymous:"yes" AND @context.isRedlink:"yes" NOT @context.isDirty:"yes"';
$queries['ck save isAnonymous=yes isRedlink=no isDirty=no'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" AND @context.isAnonymous:"yes" NOT @context.isRedlink:"yes" NOT @context.isDirty:"yes"';
$queries['ck save isAnonymous=no isRedlink=yes isDirty=no'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" NOT @context.isAnonymous:"yes" AND @context.isRedlink:"yes" NOT @context.isDirty:"yes"';
$queries['ck save isAnonymous=no isRedlink=no isDirty=no'] =
  '@message:"veTrack-v3" AND @context.action:"ck-save-button-click" NOT @context.isAnonymous:"yes" NOT @context.isRedlink:"yes" NOT @context.isDirty:"yes"';

$queries['ve save isAnonymous=yes isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ve-save-button-click" AND @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ve save isAnonymous=yes isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ve-save-button-click" AND @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';
$queries['ve save isAnonymous=no isRedlink=yes'] =
  '@message:"veTrack-v3" AND @context.action:"ve-save-button-click" NOT @context.isAnonymous:"yes" AND @context.isRedlink:"yes"';
$queries['ve save isAnonymous=no isRedlink=no'] =
  '@message:"veTrack-v3" AND @context.action:"ve-save-button-click" NOT @context.isAnonymous:"yes" NOT @context.isRedlink:"yes"';

date_default_timezone_set('utc');
$timeStamp = time() - ( $days * 60 * 60 * 24 );
$results = runQueries(
  date('Y.m.d', $timeStamp),
  $queries
);

$data['ve_date'] =  date('Y-m-d', $timeStamp);

$data['ve_c'] = $results['ck imp isAnonymous=yes isRedlink=yes'];
$data['ve_d'] = $results['ck imp isAnonymous=yes isRedlink=no'];
$data['ve_e'] = $results['ck imp isAnonymous=no isRedlink=yes'];
$data['ve_f'] = $results['ck imp isAnonymous=no isRedlink=no'];

$data['ve_g'] = $results['ve impstart isAnonymous=yes isRedlink=yes'];
$data['ve_h'] = $results['ve impstart isAnonymous=yes isRedlink=no'];
$data['ve_i'] = $results['ve impstart isAnonymous=no isRedlink=yes'];
$data['ve_j'] = $results['ve impstart isAnonymous=no isRedlink=no'];

$data['ve_k'] = $results['ve impstop isAnonymous=yes isRedlink=yes'];
$data['ve_l'] = $results['ve impstop isAnonymous=yes isRedlink=no'];
$data['ve_m'] = $results['ve impstop isAnonymous=no isRedlink=yes'];
$data['ve_n'] = $results['ve impstop isAnonymous=no isRedlink=no'];

$data['ve_o'] = $results['ck save isAnonymous=yes isRedlink=yes isDirty=yes'];
$data['ve_p'] = $results['ck save isAnonymous=yes isRedlink=no isDirty=yes'];
$data['ve_q'] = $results['ck save isAnonymous=no isRedlink=yes isDirty=yes'];
$data['ve_r'] = $results['ck save isAnonymous=no isRedlink=no isDirty=yes'];
$data['ve_s'] = $results['ck save isAnonymous=yes isRedlink=yes isDirty=no'];
$data['ve_t'] = $results['ck save isAnonymous=yes isRedlink=no isDirty=no'];
$data['ve_u'] = $results['ck save isAnonymous=no isRedlink=yes isDirty=no'];
$data['ve_v'] = $results['ck save isAnonymous=no isRedlink=no isDirty=no'];

$data['ve_w'] = $results['ve save isAnonymous=yes isRedlink=yes'];
$data['ve_x'] = $results['ve save isAnonymous=yes isRedlink=no'];
$data['ve_y'] = $results['ve save isAnonymous=no isRedlink=yes'];
$data['ve_z'] = $results['ve save isAnonymous=no isRedlink=no'];

$queryData = http_build_query($data);
$command = "curl 'https://script.google.com/macros/s/AKfycbxvTZ7qYe3RB-8sOvzywHXc_sbSGWXOeCzn5qZGSTqgI0lFz-8/exec?{$queryData}'";
$output = shell_exec ( $command );
