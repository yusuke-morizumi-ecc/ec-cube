<?php

namespace Eccube\Entity;

interface Translatable {

    /**
     * プロパティの値を指定のロケールで翻訳した文字列を返す
     *
     * @param string $prpertyName プロパティ名
     * @param string $locale ロケール
     * @return string
     */
    public function getTranslatedProperty(string $prpertyName, string $locale):string;
}
