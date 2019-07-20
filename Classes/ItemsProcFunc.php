<?php
namespace Skar\Skvideo;

/**
 * Userfunc to render alternative label for media elements
 */
class ItemsProcFunc
{


    public function user_getVideoTypeList(array &$config) {
        $optionList = [];
        $optionList[] = ["Youtube",Helper::TYPE_YOUTUBE];
        $optionList[] = ["Vimeo",Helper::TYPE_VIMEO];

        $config['items'] = $optionList;
        return $config;
    }



}
