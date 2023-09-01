; <?php die(); ?>

; PHPIDS Config.ini

; General configuration settings


[General]

    ; basic settings - customize to make the PHPIDS work at all
    filter_type     = xml

    base_path       = global_libs/IDS/
    use_base_path   = true

    filter_path     = default_filter.xml
    tmp_path        = tmp
    scan_keys       = false

    ; in case you want to use a different HTMLPurifier source, specify it here
    ; By default, those files are used that are being shipped with PHPIDS
    HTML_Purifier_Path	= vendors/htmlpurifier/HTMLPurifier.auto.php
    HTML_Purifier_Cache = vendors/htmlpurifier/HTMLPurifier/DefinitionCache/Serializer

    ; define which fields contain html and need preparation before
    ; hitting the PHPIDS rules (new in PHPIDS 0.5)
    ;html[]          = POST.__wysiwyg
    html[]          = GET.setupData
    html[]          = REQUEST.setupData

    ; define which fields contain JSON data and should be treated as such
    ; for fewer false positives (new in PHPIDS 0.5.3)
    ;json[]          = POST.__jsondata
    json[]          = GET.setupData
    json[]          = REQUEST.setupData

    ; define which fields shouldn't be monitored (a[b]=c should be referenced via a.b)
    exceptions[] = fbsr_
    exceptions[] = /^COOKIE\.fbsr_.+$/i
    exceptions[] = /^REQUEST\.fbsr_.+$/i

    exceptions[] = /^COOKIE\.fbsr_*/i
    exceptions[] = /^REQUEST\.fbsr_*/i

    exceptions[] = /^COOKIE\.*_session/i
    exceptions[] = /^REQUEST\.*_session/i

    exceptions[] = /^COOKIE\.mp_.+$/i
    exceptions[] = /^REQUEST\.mp_.+$/i
    exceptions[] = __utmz
    exceptions[] = __utmc
    exceptions[] = __gads
    exceptions[] = COOKIE.__utmz
    exceptions[] = COOKIE.__utmc
    exceptions[] = COOKIE.__gads
    exceptions[] = REQUEST.__utmz
    exceptions[] = REQUEST.__utmc
    exceptions[] = REQUEST.__gads
    exceptions[] = POST.password
    exceptions[] = POST.password_v
    exceptions[] = POST.new_pass
    exceptions[] = POST.new_password
    exceptions[] = POST.new_pass_conf
    exceptions[] = POST.old_pass
    exceptions[] = POST.old_password
    exceptions[] = REQUEST.password
    exceptions[] = REQUEST.password_v
    exceptions[] = REQUEST.new_pass
    exceptions[] = REQUEST.new_password
    exceptions[] = REQUEST.new_pass_conf
    exceptions[] = REQUEST.old_pass
    exceptions[] = REQUEST.old_password
    exceptions[] = REQUEST.recaptcha_response_field
    exceptions[] = REQUEST.recaptcha_challenge_field
    exceptions[] = POST.recaptcha_response_field
    exceptions[] = POST.recaptcha_challenge_fielded

    exceptions[] = REQUEST.adcopy_response
    exceptions[] = REQUEST.adcopy_challenge
    exceptions[] = POST.adcopy_response
    exceptions[] = POST.adcopy_challenge

    exceptions[] = REQUEST.g-recaptcha-response
    exceptions[] = POST.g-recaptcha-responser

    exceptions[] = REQUEST.appErrorMsg
    exceptions[] = POST.appErrorMsg
    exceptions[] = REQUEST.appErrorXml
    exceptions[] = POST.appErrorXml

    exceptions[] = REQUEST.mobile_receipt
    exceptions[] = POST.mobile_receipt
    exceptions[] = REQUEST.mobile_transactionID
    exceptions[] = POST.mobile_transactionID

    exceptions[] = REQUEST.reason_text
    exceptions[] = POST.reason_text

    ; These are handled manually using mysqli_real_escape_string()-function
    exceptions[] = REQUEST.message
    exceptions[] = POST.message
    exceptions[] = REQUEST.battle_description
    exceptions[] = POST.battle_description
    exceptions[] = REQUEST.oldbattle_description
    exceptions[] = POST.oldbattle_description
    exceptions[] = REQUEST.warn_message
    exceptions[] = POST.warn_message
    exceptions[] = REQUEST.login_password
    exceptions[] = POST.login_password
    exceptions[] = GET.message
    exceptions[] = REQUEST.sig
    exceptions[] = GET.sig
    exceptions[] = REQUEST.orders
    exceptions[] = POST.orders

    exceptions[] = REQUEST.nindo
    exceptions[] = POST.nindo
    exceptions[] = GET.nindo


    ; you can use regular expressions for wildcard exceptions - example: /.*foo/i

    ; PHPIDS should run with PHP 5.1.2 but this is untested - set
    ; this value to force compatibilty with minor versions
    min_php_version = 5.1.6

; If you use the PHPIDS logger you can define specific configuration here

[Logging]

    ; file logging
    path            = tmp/phpids_log.txt

    ; email logging

    ; note that enabling safemode you can prevent spam attempts,
    ; see documentation
    ; recipients[]    = nano.mathias@gmail.com
    ; subject         = "PHPIDS detected an intrusion attempt!"
    ; header          = "From: <PHPIDS> info@phpids.org"
    ; envelope        = ""
    ; safemode        = true
    ; urlencode       = true
    ; allowed_rate    = 15

    ; database logging
    ; wrapper         = "mysql:host=a4d1a923d4265cb0f86ab3c2f5c374b3c95c473f.rackspaceclouddb.com;port=3306;dbname=core3_db"
    ; user            = core3_user
    ; password        = 4EYBBdTvE2uk4eg5cASz
    ; table           = IDS_log

; If you would like to use other methods than file caching you can configure them here

[Caching]

    ; caching:      session|file|database|memcached|none
    caching         = file
    expiration_time = 600

    ; file cache
    path            = tmp/default_filter.cache

    ; database cache
    wrapper         = "mysql:host=localhost;port=3306;dbname=phpids"
    user            = phpids_user
    password        = 123456
    table           = cache

    ; memcached
    ;host           = localhost
    ;port           = 11211
    ;key_prefix     = PHPIDS
