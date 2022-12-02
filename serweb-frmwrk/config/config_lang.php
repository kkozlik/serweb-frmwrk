<?php
/*
 * $Id: config_lang.php,v 1.2 2005/07/21 16:23:03 kozlik Exp $
 */

/**
 * All the supported languages have to be listed in the array below.
 * 1. The key must be the "official" ISO 639 language code and, if required,
 *    the dialect code. It can also contain some informations about the
 *    charset (see the Russian case).
 * 2. The first of the values associated to the key is used in a regular
 *    expression to find some keywords corresponding to the language inside two
 *    environment variables.
 *    These values contains:
 *    - the "official" ISO language code and, if required, the dialect code
 *      also ('bu' for Bulgarian, 'fr([-_][[:alpha:]]{2})?' for all French
 *      dialects, 'zh[-_]tw' for Chinese traditional...);
 *    - the '|' character (it means 'OR');
 *    - the full language name.
 * 3. The second values associated to the key is the name of the file to load
 *    without the '.php' extension.
 * 4. The last values associated to the key is the language code as defined by
 *    the RFC1766.
 *
 * Beware that the sorting order (first values associated to keys by
 * alphabetical reverse order in the array) is important: 'zh-tw' (chinese
 * traditional) must be detected before 'zh' (chinese simplified) for
 * example.
 *
 * When there are more than one charset for a language, we put the -utf-8
 * first.
 *
 * For Russian, we put 1251 first, because MSIE does not accept 866
 * and users would not see anything.
 */

global $available_languages, $reference_language;

$available_languages = array(
    'en-utf-8'     => array('en([-_][[:alpha:]]{2})?|english',  'english-utf-8', 'en')
);

$reference_language = 'en-utf-8';
?>
