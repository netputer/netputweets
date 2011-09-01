<?php
/*
From http://github.com/mzsanford/twitter-text-php/blob/master/src/Twitter/Extractor.php
This file is
Copyright 2010 Mike Cochrane

Licensed under the Apache License, Version 2.0 (the "License"); you may not
use this file except in compliance with the License. You may obtain a copy of
the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
License for the specific language governing permissions and limitations under
the License.
*/

class Twitter_Extractor {

    public function extractAll($tweet) {
        return array(
                'hashtags' => $this->extractHashtags($tweet),
                'urls' =>     $this->extractURLS($tweet),
                'mentions' => $this->extractMentionedScreennames($tweet),
                'replyto' =>  $this->extractReplyScreenname($tweet)
                );
    }

    public function extractHashtags($tweet) {
        preg_match_all('$(^|[^0-9A-Z&/]+)([#＃]+)([0-9A-Z_]*[A-Z_]+[a-z0-9_üÀ-ÖØ-öø-ÿ]*)$i', $tweet, $matches);
        return $matches[3];
    }

    public function extractURLS($tweet) {
        $URL_VALID_PRECEEDING_CHARS = "(?:[^/\"':!=]|^|\\:)";
        $URL_VALID_DOMAIN = "(?:[\\.-]|[^\\p{P}\\s])+\\.[a-z]{2,}(?::[0-9]+)?";
        $URL_VALID_URL_PATH_CHARS = "[a-z0-9!\\*'\\(\\);:&=\\+\\$/%#\\[\\]\\-_\\.,~@]";
        // Valid end-of-path chracters (so /foo. does not gobble the period).
        //   1. Allow ) for Wikipedia URLs.
        //   2. Allow =&# for empty URL parameters and other URL-join artifacts
        $URL_VALID_URL_PATH_ENDING_CHARS = "[a-z0-9\\)=#/]";
        $URL_VALID_URL_QUERY_CHARS = "[a-z0-9!\\*'\\(\\);:&=\\+\\$/%#\\[\\]\\-_\\.,~]";
        $URL_VALID_URL_QUERY_ENDING_CHARS = "[a-z0-9_&=#]";
        $VALID_URL_PATTERN_STRING = '$(' .                                 //  $1 total match
        "(" . $URL_VALID_PRECEEDING_CHARS . ")" .                       //  $2 Preceeding chracter
        "(" .                                                           //  $3 URL
          "(https?://|www\\.)" .                                        //  $4 Protocol or beginning
          "(" . $URL_VALID_DOMAIN . ")" .                               //  $5 Domain(s) and optional port number
          "(/" . $URL_VALID_URL_PATH_CHARS . "*" .                      //  $6 URL Path
                 $URL_VALID_URL_PATH_ENDING_CHARS . "?)?" .
          "(\\?" . $URL_VALID_URL_QUERY_CHARS . "*" .                   //  $7 Query String
                  $URL_VALID_URL_QUERY_ENDING_CHARS . ")?" .
        ")" .
        ')$i';

        preg_match_all($VALID_URL_PATTERN_STRING, $tweet, $matches);
        return $matches[3];
    }

    /**
     * Extract @username references from Tweet text. A mention is an occurance of @username anywhere in a Tweet.
     *
     * @param  String text of the tweet from which to extract usernames
     * @return Array of usernames referenced (without the leading @ sign)
     */
    public function extractMentionedScreennames($tweet) {
        preg_match_all('/(^|[^a-zA-Z0-9_])[@＠]([a-zA-Z0-9_]{1,20})(?=(.|$))/', $tweet, $matches);
        $usernames = array();
        for ($i = 0; $i < sizeof($matches[2]); $i += 1) {
          if (! preg_match('/^[@＠]/', $matches[3][$i])) {
            array_push($usernames, $matches[2][$i]);
          }
        }
        return $usernames;
    }

    public function extractReplyScreenname($tweet) {
        /* Single byte whitespace characters */
        $whitespace  = '[';
        $whitespace .= "\x09-\x0D";     # 0x0009-0x000D White_Space # Cc   [5] <control-0009>..<control-000D>
        $whitespace .= "\x20";          # 0x0020 White_Space # Zs       SPACE
        $whitespace .= "\x85";          # 0x0085 White_Space # Cc       <control-0085>
        $whitespace .= "\xA0";          # 0x00A0 White_Space # Zs       NO-BREAK SPACE
        $whitespace .= "]|";

        /* Mutli byte whitespace characters */
        $whitespace .= "\xe1\x9a\x80|";                           # 0x1680White_Space # Zs       OGHAM SPACE MARK
        $whitespace .= "\xe1\xa0\x8e|";                           # 0x180E White_Space # Zs       MONGOLIAN VOWEL SEPARATOR
        $whitespace .= "\xe2\x80[\x80-\x8a,\xa8,\xa9,\xaf\xdf]|"; # 0x2000-0x200A White_Space # Zs  [11] EN QUAD..HAIR SPACE
                                                                  # 0x2028 White_Space # Zl       LINE SEPARATOR
                                                                  # 0x2029 White_Space # Zp       PARAGRAPH SEPARATOR
                                                                  # 0x202F White_Space # Zs       NARROW NO-BREAK SPACE
                                                                  # 0x205F White_Space # Zs       MEDIUM MATHEMATICAL SPACE
        $whitespace .= "\xe3\x80\x80";                            #0x3000 White_Space # Zs       IDEOGRAPHIC SPACE

        preg_match('/^(' . $whitespace . ')*[@＠]([a-zA-Z0-9_]{1,20})/', $tweet, $matches);
        return isset($matches[2]) ? $matches[2] : '';
    }
}