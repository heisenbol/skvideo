# skvideo
TYPO3 content element to cache thumbnails of youtube/vimeo videos and play them after user confirmation. Responsive display. Works with or without fluid_styled_content. No other dependencies (no jquery etc).

## How it works
The **editor** inserts a skvideo content element into a page. He needs to specify
* the youtube/vimeo video code
* the type of the video (youtube or vimeo)
* whether to include the video title embedded in the preview image
* the width vs height ratio (16:9 or 4:3)
* optionally set a maximum width of the video on the page (otherwise it defaults to full width display)


The **end user** visits the page. The plugin fetches and stores a copy of the video thumbnail on the server. So the user's browser downloads the thumbnail from the site server, and not from the video provider. 

If the user presses the play button of the video, he is being presented with a customizable notice and is asked to confirm that he wants to play the video. Optionally, the user can check a checkbox to store his decision via a cookie for 30 days. Separate cookies for Youtube and Vimeo are used.

If the user confirms, the video is played. In case the user had checked the checkbox to store his decission, the video is played without the notice.


## How to install
The content element needs css_styled_content or fluid_styled_content in order to work. So one of these extensions must be enabled on your site.

Additionally, the SK Video static template must have been included into your TYPO3 template.

## Configuration
### Preview Image size
Preview images that are retrieved from Youtube or Vimeo are resized to a max of 500x500 pixel. You can change this size via constants. For example, to resize to 700x600 pixel:

`plugin.tx_skvideo.settings.max_preview_width = 700`

`plugin.tx_skvideo.settings.max_preview_height = 600`

### CSS
The extension comes with a default css file located at Resources/Public/Css/styles.css. If you want to adapt it, you copy it 
and include your changed version in your site. In this case, you can instruct the extension to not include it's own css file via a constant:

`plugin.tx_skvideo.settings.nocss = 1`


### Texts
You can adapt the text of the confirmation modal dialog displayed to the user. 

#### Using Typoscript in setup section
There are 5 keys that you can adapt:

`message`: This is the text the user is asked to agree before viewing the video. You can use HTML markup in here. You can also use a placeholder VIDEOPROVIDER which will be replaced with YOUTUBE or VIMEO according to the current video.

`rememberme`: The text for the remember my decision checkbox (only if the remember me option is not disabled via constant)

`remembermesession`: The text for the remember my decision checkbox in case the cookie is set be stored only for the current session  (only if the remember me option is not disabled via constant)

`cancel`: The text for the cancel button

`continue`: The text for the agree button 

To change these texts, use one or more of these keys in your setup section

`plugin.tx_skvideo._LOCAL_LANG.default.message` = New custom text VIDEOPROVIDER for default language

`plugin.tx_skvideo._LOCAL_LANG.en.message` = New custom text custom text for en

`plugin.tx_skvideo._LOCAL_LANG.de.message` = New custom text custom text for de


#### Using constants
You can also change these texts via constants, which makes sense only for single language sites:
 
`plugin.tx_skvideo.settings.message`: This is the text the user is asked to agree before viewing the video. You can use HTML markup in here.

`plugin.tx_skvideo.settings.rememberme`: The text for the remember my decision checkbox. The number of days to store the cookies must be specified manually

`plugin.tx_skvideo.settings.remembermesession`: The text for the remember my decision checkbox for session only storage

`plugin.tx_skvideo.settings.cancel`: The text for the cancel button

`plugin.tx_skvideo.settings.continue`: The text for the agree button

### Other settings
To disable the "remember my decision" option, set the constant
`plugin.tx_skvideo.settings.disablerememberme = 1`

To change the number of days the remember cookie is stored e.g. to 5 days, set
`plugin.tx_skvideo.settings.remembermedays = 5`

Default is 30 days. Set it to 0 to remember it only for the current session. Maximum is 180 days.



### Thumbnail cache lifetime and youtube playlists API key
The extension has 2 options to control the lifetime of the preview images and the associated video texts (title etc). Essentially they control how long these should be cached. Currently both values default to 1209600 seconds which is 2 weeks.

Since version 1.1.0, you can also embed youtube playlists. But for playlists you need a valid Google API key for YouTube Data API v3.

You can change these options under Settings, Extension Configuration for skvideo.
