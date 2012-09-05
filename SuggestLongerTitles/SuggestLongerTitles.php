<?php

if( !defined( 'MEDIAWIKI' ) ) {
    echo( "This file is part of an extension to the MediaWiki software and cannot be used standalone.\n" );
    die( 1 );
}

$wgExtensionCredits['parserhook'][] = array(
    'path' => __FILE__,
    'name' => 'SuggestLongerTitles',
    'author' => 'Yangmun Choi, UCLA',
    'description' => 'Simple check for truncated wiki URLs.',
    'version' => '1.0.0'
);

$wgHooks['ArticleViewHeader'][] = 'wfSuggestIfNoContent';
/**
 *  Checks if a page (based on its last-modified time) has ever been editied 
 *  before. If so, then will do a database search for suggestable links.
 *  Hook to onArticleViewHeader
 *  @param $article the article (object)
 *  @param $outputDone If there is no more output to output, set $outputDone 
 *      to true.
 *  @param $pcache If you want the parser cache to try retrieving cached 
 *      results, set $pcache to done.
 *  @return boolean true if function finished.
 **/
function wfSuggestIfNoContent( &$article, &$outputDone, &$pCache ) {
    global $wgOut;

    if( !$article->mTitle->mArticleID ) {
        $db = wfGetDB( DB_SLAVE );
        $res = $db->select( 
            'page', 
            'page_title',
            array(
                'page_namespace' => 0,
                "page_title LIKE '" 
                    . $db->strencode( $article->mTitle->mDbkeyform ) 
                    . "%'"
            ),
            'wfSuggestIfNoContent',
            array ( 'LIMIT' => 2 ) 
        );

        $links = array();
        while( $row = $db->fetchObject( $res ) ) {
            // TODO see what medaiwiki uses to generate URL
            $links[] = str_replace( '_', ' ', $row->page_title );
        }

        if( !empty( $links ) ) {
            $wgOut->addWikiText( 'The article you were looking for could not '
                . 'be found.' );
            $wgOut->addWikiText( 'Maybe you were looking for the following '
                . 'article(s):' );
            foreach( $links as $link ) {
                $wgOut->addWikiText( "* [[$link]]" );
            }

            // No need to render editing dialog
            $outputDone = true;
        }
    }

    return true;
}
