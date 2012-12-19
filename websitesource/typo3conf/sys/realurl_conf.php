<?php
$TYPO3_CONF_VARS['EXTCONF']['realurl']['_DEFAULT'] = array(
  // ==========================================================================
  // = init ===================================================================
  // ==========================================================================
  'init' => array(
    'enableCHashCache'        => 1,
    'enableUrlDecodeCache'    => 1,
    'enableUrlEncodeCache'    => 0,
    'disablePathCache'        => 1,
// deactivated as we use pageNotFound_handling
//    'postVarSet_failureMode'  => 'redirect_goodUpperDir',  // falls URL fehlschlaegt, wird auf die Startseite weitergeleitet
  ),


  // ==========================================================================
  // = pagePath ===============================================================
  // ==========================================================================
  'pagePath' => array(
    'type'                    => 'user',
    'userFunc'                => 'EXT:realurl/class.tx_realurl_advanced.php:&tx_realurl_advanced->main',
    'spaceCharacter'          => '_',
    'languageGetVar'          => 'L',
    'expireDays'              => '32',
    // 'disablePathCache'     => '1',
    'autoUpdatePathCache'     => '0',
    'rootpage_id'             => '2',
    'segTitleFieldList'       => 'tx_realurl_pathsegment,nav_title,alias,title',
    // 'segTitleFieldList'       => 'nav_title,title',
    'dontResolveShortcuts'    => true,
    // 'excludePageIds'          => '266,268,271',
  ),


  // ==========================================================================
  // = fileName ===============================================================
  // ==========================================================================
  'fileName' => array(
    'defaultToHTMLsuffixOnPrev' => 1,
    'index'                     => array(
      'page.html' => array(
        'keyValues' => array(
          'type' => 1,
        ),
      ),
    ),
  ),


  // ==========================================================================
  // = preVars ================================================================
  // ==========================================================================
  'preVars' => array(
    // = Language =============================================================
    array(
      'GETvar'        => 'L',
      'valueDefault'  => 'de',
      'noMatch'       => 'bypass',
      'valueMap'      => array(
        'de' => '0',
        'en' => '1',
      ),
    ),
    // = Cache ================================================================
    array(
      'GETvar'    => 'no_cache',
      'noMatch'   => 'bypass',
      'valueMap'  => array(
        'no_cache' => '1',
      ),
    ),
    array(
      'GETvar'    => 'direct',
      'noMatch'   => 'bypass',
      'valueMap'  => array(
        'direct' => '1',
      ),
    ),
    array(
      'GETvar'    => 'uncached',
      'noMatch'   => 'bypass',
      'valueMap'  => array(
        'uncached' => '1',
      ),
    ),
  ),


  // ==========================================================================
  // = fixedPostVarSets =======================================================
  // ==========================================================================
  'fixedPostVarSets' => array(
  ),


  // ==========================================================================
  // = postVarSets ============================================================
  // ==========================================================================
  'postVarSets' => array(
    '_DEFAULT' => array(
    ),
  ),  
);

// $TYPO3_CONF_VARS["FE"]["pageNotFound_handling"] = 'READFILE:fileadmin/templates/error/_404.html';
// $TYPO3_CONF_VARS["FE"]["pageNotFound_handling"] = '/404/';
$TYPO3_CONF_VARS["FE"]["pageNotFound_handling"] = '/404.html';
$TYPO3_CONF_VARS["FE"]["pageNotFound_handling_statheader"] = 'HTTP/1.1 404 Not Found';
$TYPO3_CONF_VARS["FE"]["pageUnavailable_handling"] = 'READFILE:wartungsarbeiten.html';
// $TYPO3_CONF_VARS["SYS"]["enable_DLOG"] = 'true';

?>