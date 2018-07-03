<?php

use Codeception\Util\Fixtures;
use Page\Admin\CategoryCsvUploadPage;
use Page\Admin\CategoryManagePage;
use Page\Admin\CsvSettingsPage;
use Page\Admin\ClassCategoryManagePage;
use Page\Admin\ClassNameManagePage;
use Page\Admin\ProductClassEditPage;
use Page\Admin\ProductCsvUploadPage;
use Page\Admin\ProductManagePage;
use Page\Admin\ProductEditPage;

/**
 * @group admin
 * @group admin01
 * @group product
 * @group ea3
 */
class EA03ProductCest
{
    const ページタイトル = '#main .page-header';
    const ページタイトルStyleGuide = '.c-pageTitle';

    public function _before(\AcceptanceTester $I)
    {
        // すべてのテストケース実施前にログインしておく
        // ログイン後は管理アプリのトップページに遷移している
        $I->loginAsAdmin();
    }

    public function _after(\AcceptanceTester $I)
    {
    }

    public function product_商品検索(\AcceptanceTester $I)
    {
        $I->wantTo('EA0301-UC01-T01 (& UC01-T02) 商品検索');

        ProductManagePage::go($I)->検索('フォーク');

        $I->see("検索結果：1件が該当しました", ProductManagePage::$検索結果_メッセージ);
        $I->see("ディナーフォーク", ProductManagePage::$検索結果_一覧);

        ProductManagePage::go($I)->検索('gege@gege.com');
        $I->see('検索結果：0件が該当しました', ProductManagePage::$検索結果_メッセージ);
    }

    public function product_商品検索エラー(\AcceptanceTester $I)
    {
        $I->wantTo('EA0301-UC01-T03 商品検索 エラー');

        // バリデーションエラーが発生するフォーム項目がないため, ダミーのステータスを作っておく
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = Fixtures::get('entityManager');
        $ProductStatus = new \Eccube\Entity\Master\ProductStatus();
        $ProductStatus->setName('ダミー');
        $ProductStatus->setSortNo(999);
        $ProductStatus->setId(999);
        $em->persist($ProductStatus);
        $em->flush();

        // 商品マスターを表示
        $page = ProductManagePage::go($I);

        // ダミーのステータスを削除する
        $em->remove($ProductStatus);
        $em->flush();

        // 存在しないステータスで検索するため, `有効な値ではありません`のバリデーションエラーが発生するはず
        $page->詳細検索_ステータス(999);
        $I->see('検索条件に誤りがあります。', ProductManagePage::$検索結果_エラーメッセージ);
    }

    public function product_規格確認のポップアップ表示(\AcceptanceTester $I)
    {
        $I->wantTo('EA0301-UC01-T03 規格確認のポップアップを表示');

        ProductManagePage::go($I)
            ->検索()
            ->規格確認ボタンをクリック()
            ->規格確認をキャンセル();

        $I->dontSeeElement(['css' => 'div.modal.show']);
    }

    public function product_ポップアップから規格編集画面に遷移(\AcceptanceTester $I)
    {
        $I->wantTo('EA0301-UC01-T04 ポップアップから規格編集画面に遷移');

        ProductManagePage::go($I)
            ->検索()
            ->規格確認ボタンをクリック()
            ->規格編集画面に遷移();

        $I->see('商品登録（規格設定）商品管理', self::ページタイトルStyleGuide);
    }

    public function product_商品検索結果無(\AcceptanceTester $I)
    {
        $I->wantTo('EA0301-UC01-T02 商品検索 検索結果なし');

        ProductManagePage::go($I)->検索('お箸');

        $I->see("検索条件に合致するデータが見つかりませんでした", ProductManagePage::$検索結果_結果なしメッセージ);
    }

    /**
     * @env firefox
     * @env chrome
     */
    public function product_CSV出力(\AcceptanceTester $I)
    {
        $I->wantTo('EA0301-UC02-T01 CSV出力');

        $findProducts = Fixtures::get('findProducts');
        $Products = $findProducts();
        ProductManagePage::go($I)
            ->検索()
            ->CSVダウンロード();

        $I->see("検索結果：".count($Products)."件が該当しました", ProductManagePage::$検索結果_メッセージ);

        $ProductCSV = $I->getLastDownloadFile('/^product_\d{14}\.csv$/');
        $I->assertGreaterOrEquals(count($Products), count(file($ProductCSV)), '検索結果以上の行数があるはず');
    }

    public function product_CSV出力項目設定(\AcceptanceTester $I)
    {
        $I->wantTo('EA0301-UC02-T02 CSV出力項目設定');

        ProductManagePage::go($I)
            ->検索()
            ->CSV出力項目設定();

        $I->see('CSV出力項目設定基本情報設定', self::ページタイトルStyleGuide);
        $value = $I->grabValueFrom(CsvSettingsPage::$CSVタイプ);
        $I->assertEquals('1', $value);
    }

    public function product_一覧からの規格編集規格なし失敗(\AcceptanceTester $I)
    {
        $I->wantTo('EA0310-UC01-T02 一覧からの規格編集 規格なし 失敗');

        ProductManagePage::go($I)
            ->検索('規格なし商品')
            ->検索結果_選択(1);

        ProductEditPage::at($I)
            ->規格管理();

        ProductClassEditPage::at($I)
            ->規格設定();

        $I->seeElement(['css' => '#product_class_matrix_class_name1:invalid']); //規格1がエラー
        $I->dontSeeElement(ProductClassEditPage::$規格一覧); // 規格編集行が表示されていない
    }

    public function product_一覧からの規格編集規格なし_(\AcceptanceTester $I)
    {
        $I->wantTo('EA0310-UC01-T01 一覧からの規格編集 規格なし');

        ProductManagePage::go($I)
            ->検索('規格なし商品')
            ->検索結果_選択(1);

        ProductEditPage::at($I)
            ->規格管理();

        $ProductClassEditPage = ProductClassEditPage::at($I)
            ->入力_規格1('材質')
            ->規格設定();

        $I->see('3 件の規格の組み合わせがあります', 'div.c-contentsArea__cols > div > div > form div.card-header > div > div.col-6 > span');

        $ProductClassEditPage
            ->選択(1)
            ->入力_在庫数無制限(1)
            ->入力_販売価格(1, 1000)
            ->選択(2)
            ->入力_在庫数無制限(2)
            ->入力_販売価格(2, 1000)
            ->選択(3)
            ->入力_在庫数無制限(3)
            ->入力_販売価格(3, 1000)
            ->登録();

        $I->waitForElement(ProductClassEditPage::$登録完了メッセージ);
        $I->see('商品規格を登録しました。', ProductClassEditPage::$登録完了メッセージ);
        $I->seeElement(ProductClassEditPage::$初期化ボタン);
    }

    public function product_一覧からの規格編集規格あり2(\AcceptanceTester $I)
    {
        $I->wantTo('EA0310-UC02-T02 一覧からの規格編集 規格あり2');

        $findProducts = Fixtures::get('findProducts');
        $Products = array_filter($findProducts(), function ($Product) {
            return $Product->hasProductClass();
        });
        $Product = array_pop($Products);
        ProductManagePage::go($I)
            ->検索($Product->getName())
            ->検索結果_選択(1);

        ProductEditPage::at($I)
            ->規格管理();

        ProductClassEditPage::at($I)
            ->登録();

        $I->see('商品規格を更新しました。', ProductClassEditPage::$登録完了メッセージ);
    }

    public function product_一覧からの商品複製(\AcceptanceTester $I)
    {
        $I->wantTo('EA0310-UC05-T02 一覧からの商品複製');

        $findProducts = Fixtures::get('findProducts');
        $Products = array_filter($findProducts(), function ($Product) {
            return $Product->hasProductClass();
        });
        $Product = array_pop($Products);
        ProductManagePage::go($I)
            ->検索($Product->getName())
            ->検索結果_複製(1);

        $I->acceptPopup();
    }

    /**
     * ATTENTION 削除すると後続の規格編集関連のテストが失敗するため、最後に実行する
     */
    public function product_一覧からの規格編集規格あり1(\AcceptanceTester $I)
    {
        $I->wantTo('EA0310-UC02-T01 一覧からの規格編集 規格あり1');

        $findProducts = Fixtures::get('findProducts');
        $Products = array_filter($findProducts(), function ($Product) {
            return $Product->hasProductClass();
        });
        $Product = array_pop($Products);
        ProductManagePage::go($I)
            ->検索($Product->getName())
            ->検索結果_選択(1);

        ProductEditPage::at($I)
            ->規格管理();

        $I->seeElement(ProductClassEditPage::$規格一覧);

        ProductClassEditPage::at($I)
            ->規格初期化();

        $I->see('商品規格を削除しました', ProductClassEditPage::$登録完了メッセージ);
        $I->dontSeeElement(ProductClassEditPage::$規格一覧);
    }

    public function product_商品登録非公開(\AcceptanceTester $I)
    {
        $I->wantTo('EA0302-UC01-T01 商品登録 非公開');

        ProductEditPage::go($I)
            ->入力_商品名('test product1')
            ->入力_販売価格('1000')
            ->入力_カテゴリ(1)
            ->登録();

        $I->see('登録が完了しました。', ProductEditPage::$登録結果メッセージ);
    }

    public function product_商品登録公開(\AcceptanceTester $I)
    {
        $I->wantTo('EA0302-UC01-T02 商品登録 公開');

        ProductEditPage::go($I)
            ->入力_商品名('test product2')
            ->入力_販売価格('1000')
            ->入力_カテゴリ(1)
            ->入力_公開()
            ->登録();

        $I->see('登録が完了しました。', ProductEditPage::$登録結果メッセージ);
    }

    public function product_商品編集規格なし(\AcceptanceTester $I)
    {
        $I->wantTo('EA0302-UC01-T03 商品編集 規格なし');

        ProductManagePage::go($I)
            ->検索('test product1')
            ->検索結果_選択(1);

        ProductEditPage::at($I)
            ->入力_商品名('test product11')
            ->入力_カテゴリ(1)
            ->入力_カテゴリ(2)
            ->登録();

        $I->see('登録が完了しました。', ProductEditPage::$登録結果メッセージ);
    }

    public function product_商品編集規格あり(\AcceptanceTester $I)
    {
        $I->wantTo('EA0302-UC01-T04 商品編集 規格あり');

        // 規格なし商品では商品種別等が編集可能
        ProductManagePage::go($I)
            ->検索('パーコレーター')
            ->検索結果_選択(1);
        ProductEditPage::at($I);

        $I->click(['css' => '#basicConfig > div > div:nth-child(7) > div.col > div.d-inline-block.mb-2 > a']);
        $I->seeElement(ProductEditPage::$販売種別);
        $I->seeElement(ProductEditPage::$販売価格);
        $I->waitForElement(ProductEditPage::$通常価格);
        $I->seeElement(ProductEditPage::$在庫数);
        $I->waitForElement(ProductEditPage::$商品コード);
        $I->seeElement(ProductEditPage::$販売制限数);
        $I->seeElement(ProductEditPage::$お届可能日);

        // 規格あり商品では商品種別等が編集不可
        ProductManagePage::go($I)
            ->検索('ディナーフォーク')
            ->検索結果_選択(1);
        $ProductEditPage = ProductEditPage::at($I);

        $I->dontSeeElements([
            ProductEditPage::$販売種別,
            ProductEditPage::$販売価格,
            ProductEditPage::$通常価格,
            ProductEditPage::$在庫数,
            ProductEditPage::$商品コード,
            ProductEditPage::$販売制限数,
            ProductEditPage::$お届可能日
        ]);

        $ProductEditPage->登録();
        $I->see('登録が完了しました。', ProductEditPage::$登録結果メッセージ);
    }

    public function product_新製品はタグを持っています(\AcceptanceTester $I)
    {
        $I->wantTo('EA0302-UC01-T05-タグを商品に追加する');

        ProductEditPage::go($I)
            ->入力_商品名("規格なし商品")
            ->入力_販売価格(50000)
            ->クリックして開くタグリスト()
            ->クリックして選択タグ(2)
            ->クリックして選択タグ(3)
            ->クリックして選択タグ(4)
            ->登録();
        $I->see('登録が完了しました。', 'div.c-container > div.c-contentsArea > div.alert');

        $I->seeElement(['xpath' => '//*[@id="tag"]/div/div[1]/button']);
        $I->seeElement(['xpath' => '//*[@id="tag"]/div/div[2]/button']);
        $I->seeElement(['xpath' => '//*[@id="tag"]/div/div[3]/button']);
    }

    public function product_一覧からの商品削除(\AcceptanceTester $I)
    {
        $I->wantTo('EA0310-UC05-T03 一覧からの商品削除');

        ProductManagePage::go($I)
            ->検索('')
            ->検索結果_削除(1)
            ->wait()
            ->Accept_削除(1);
    }

    public function product_商品の一括削除_正常(\AcceptanceTester $I)
    {
        $I->wantTo('EA0302-UC05-T04 商品の一括削除(正常)');

        $createProduct = Fixtures::get('createProduct');
        foreach (range(1, 5) as $i) {
            $createProduct("一括削除用_${i}");
        }
        $ProductManagePage = ProductManagePage::go($I)
            ->検索('一括削除用')
            ->すべて選択();

        $I->see("検索結果：5件が該当しました", ProductManagePage::$検索結果_メッセージ);

        $ProductManagePage
            ->完全に削除()
            ->一括削除完了();

        $I->see("検索結果：0件が該当しました", ProductManagePage::$検索結果_メッセージ);
    }



    public function product_商品の一括削除_削除エラー(\AcceptanceTester $I)
    {
        $I->wantTo('EA0302-UC05-T04 商品の一括削除(正常)');

        $createProduct = Fixtures::get('createProduct');
        $createOrders = Fixtures::get('createOrders');

        $timestamp = time();
        // 受注に紐付いていない商品と紐付いている商品を作成
        foreach (range(1, 5) as $i) {
            $createProduct("一括削除用_${timestamp}_受注なし_${i}");
        }
        $Customer = (Fixtures::get('createCustomer'))();
        foreach (range(1, 5) as $i) {
            $Product = $createProduct("一括削除用_${timestamp}_受注あり_${i}");
            $createOrders($Customer, 1, $Product->getProductClasses()->toArray());
        }

        $ProductManagePage = ProductManagePage::go($I)
            ->検索("一括削除用_${timestamp}")
            ->すべて選択();

        $I->see("検索結果：10件が該当しました", ProductManagePage::$検索結果_メッセージ);
        $I->see("一括削除用_${timestamp}_受注あり", ProductManagePage::$検索結果_一覧);
        $I->see("一括削除用_${timestamp}_受注なし", ProductManagePage::$検索結果_一覧);

        $ProductManagePage->完全に削除();

        $I->see("一括削除用_${timestamp}_受注あり_1", ProductManagePage::$一括削除エラー);
        $I->see("一括削除用_${timestamp}_受注あり_2", ProductManagePage::$一括削除エラー);
        $I->see("一括削除用_${timestamp}_受注あり_3", ProductManagePage::$一括削除エラー);
        $I->see("一括削除用_${timestamp}_受注あり_4", ProductManagePage::$一括削除エラー);
        $I->see("一括削除用_${timestamp}_受注あり_5", ProductManagePage::$一括削除エラー);

        $ProductManagePage->一括削除完了();

        $I->see("検索結果：5件が該当しました", ProductManagePage::$検索結果_メッセージ);
        $I->see("一括削除用_${timestamp}_受注あり", ProductManagePage::$検索結果_一覧);
        $I->dontSee("一括削除用_${timestamp}_受注なし", ProductManagePage::$検索結果_一覧);
    }

    public function product_規格登録_(\AcceptanceTester $I)
    {
        $I->wantTo('EA0303-UC01-T01 規格登録');

        ClassNameManagePage::go($I)
            ->入力_管理名('backend test class1')
            ->入力_表示名('display test class1')
            ->規格作成();

        $I->see('規格を保存しました。', ClassNameManagePage::$登録完了メッセージ);
    }

    public function product_規格登録未登録時(\AcceptanceTester $I)
    {
        $I->wantTo('EA0303-UC01-T02 規格登録 未登録時');
        // TODO [fixture] 規格が1件も登録されていない状態にする
    }

    public function product_規格編集(\AcceptanceTester $I)
    {
        $I->wantTo('EA0303-UC02-T01 規格編集');

        $ProductClassPage = ClassNameManagePage::go($I)->一覧_編集(2);

        $backendValue = $I->grabValueFrom(ClassNameManagePage::$管理名編集3);
        $I->assertEquals('backend test class1', $backendValue);

        $displayValue = $I->grabValueFrom(ClassNameManagePage::$表示名編集3);
        $I->assertEquals('display test class1', $displayValue);

        $ProductClassPage->規格編集(2);

        $I->see('規格を保存しました。', ClassNameManagePage::$登録完了メッセージ);
        // remove added class
        ClassNameManagePage::go($I)->一覧_削除(1)
            ->acceptModal(1);
    }

    public function product_規格削除(\AcceptanceTester $I)
    {
        $I->wantTo('EA0303-UC03-T01 規格削除');

        // Create a class name for test
        ClassNameManagePage::go($I)
            ->入力_管理名('backend test class1')
            ->入力_表示名('display test class1')
            ->規格作成();

        ClassNameManagePage::go($I)->一覧_削除(1)
            ->acceptModal(1);

        $I->see('規格を削除しました。', ClassNameManagePage::$登録完了メッセージ);
    }

    public function product_規格表示順の変更(\AcceptanceTester $I)
    {
        $I->wantTo('EA0308-UC01-T01 規格表示順の変更');

        $ProductClassPage = ClassNameManagePage::go($I);
        $I->see("サイズ", $ProductClassPage->一覧_名称(1));
        $I->see("材質", $ProductClassPage->一覧_名称(2));

        $ProductClassPage->一覧_下に(1);
        $I->see("材質", $ProductClassPage->一覧_名称(1));
        $I->see("サイズ", $ProductClassPage->一覧_名称(2));

        $ProductClassPage->一覧_上に(2);
        $I->see("サイズ", $ProductClassPage->一覧_名称(1));
        $I->see("材質", $ProductClassPage->一覧_名称(2));
    }

    public function product_分類表示順の変更(\AcceptanceTester $I)
    {
        $I->wantTo('EA0311-UC01-T01 分類表示順の変更');

        ClassNameManagePage::go($I)
            ->一覧_分類登録(1);

        $ProductClassCategoryPage = ClassCategoryManagePage::at($I);
        $I->see('150cm', $ProductClassCategoryPage->一覧_名称(1));
        $I->see('170mm', $ProductClassCategoryPage->一覧_名称(2));
        $I->see('120mm', $ProductClassCategoryPage->一覧_名称(3));

        $ProductClassCategoryPage->一覧_下に(1);
        $I->see('170mm', $ProductClassCategoryPage->一覧_名称(1));
        $I->see('150cm', $ProductClassCategoryPage->一覧_名称(2));
        $I->see('120mm', $ProductClassCategoryPage->一覧_名称(3));

        $ProductClassCategoryPage->一覧_下に(2);
        $I->see('170mm', $ProductClassCategoryPage->一覧_名称(1));
        $I->see('120mm', $ProductClassCategoryPage->一覧_名称(2));
        $I->see('150cm', $ProductClassCategoryPage->一覧_名称(3));

        $ProductClassCategoryPage->一覧_上に(3);
        $I->see('170mm', $ProductClassCategoryPage->一覧_名称(1));
        $I->see('150cm', $ProductClassCategoryPage->一覧_名称(2));
        $I->see('120mm', $ProductClassCategoryPage->一覧_名称(3));

        $ProductClassCategoryPage->一覧_上に(2);
        $I->see('150cm', $ProductClassCategoryPage->一覧_名称(1));
        $I->see('170mm', $ProductClassCategoryPage->一覧_名称(2));
        $I->see('120mm', $ProductClassCategoryPage->一覧_名称(3));
    }

    public function product_分類登録(\AcceptanceTester $I)
    {
        $I->wantTo('EA0304-UC01-T01(& UC01-T02/UC02-T01/UC03-T01) 分類登録/編集/削除');

        $ProductClassPage = ClassNameManagePage::go($I)
            ->入力_管理名('test class2')
            ->入力_表示名('test class2')
            ->規格作成();

        $I->see('規格を保存しました。', ClassNameManagePage::$登録完了メッセージ);

        $ProductClassPage->一覧_分類登録(1);
        $I->see('test class2', '#page_admin_product_class_category > div > div.c-contentsArea > div.c-contentsArea__cols > div > div.c-primaryCol > div:nth-child(1) > div.card-body > div:nth-child(2) > div:nth-child(2) > span');

        // Create a class category
        $ProductClassCategoryPage = ClassCategoryManagePage::at($I)
            ->入力_分類名('test class2 category1')
            ->分類作成();

        $I->see('分類を保存しました。', ClassCategoryManagePage::$登録完了メッセージ);
        $I->see('test class2 category1', $ProductClassCategoryPage->一覧_名称(1));

        // Edit class category 1
        $ProductClassCategoryPage->一覧_編集(1)
            ->一覧_入力_分類名(1, 'edit class category')
            ->一覧_分類作成(1);

        $I->see('分類を保存しました。', ClassCategoryManagePage::$登録完了メッセージ);
        $I->see('edit class category', $ProductClassCategoryPage->一覧_名称(1));

        // delete test
        $ProductClassCategoryPage->一覧_削除(1)
            ->acceptModal(1);

        $I->see('分類を削除しました。', ClassCategoryManagePage::$登録完了メッセージ);
    }

    public function product_カテゴリ登録(\AcceptanceTester $I)
    {
        $I->wantTo('EA0305-UC01-T01(& UC01-T02/UC02-T01/UC04-T01) カテゴリ登録/編集/削除');

        $CategoryPage = CategoryManagePage::go($I)
            ->入力_カテゴリ名('test category1')
            ->カテゴリ作成();

        $I->see('カテゴリを保存しました。', CategoryManagePage::$登録完了メッセージ);

        $CategoryPage->一覧_編集(2);

        $I->seeElement('body > div > div.c-contentsArea > div.c-contentsArea__cols > div.c-contentsArea__primaryCol > div > div > div > div > ul > li:nth-child(2) > form.mode-edit');

        $CategoryPage->一覧_インライン編集_カテゴリ名(2, 'test category11')
            ->一覧_インライン編集_決定(2);

        $I->see('カテゴリを保存しました。', CategoryManagePage::$登録完了メッセージ);

        // csv EA0305-UC04-T01
        $CategoryPage
            ->CSVダウンロード実行();
        /* csvがダウンロードされたかは確認不可 */

        // csv EA0305-UC04-T02
        $CategoryPage->CSV出力項目設定();

        CsvSettingsPage::at($I);
        $value = $I->grabValueFrom(CsvSettingsPage::$CSVタイプ);
        $I->assertEquals('5', $value);

        // サブカテゴリ EA0305-UC01-03 & UC01-04
        $CategoryPage = CategoryManagePage::go($I)
            ->一覧_選択(2);

        $I->see('test category11', CategoryManagePage::$パンくず_1階層);

        $CategoryPage
            ->入力_カテゴリ名('test category11-1')
            ->カテゴリ作成();
        $I->see('カテゴリを保存しました。', CategoryManagePage::$登録完了メッセージ);

        // カテゴリ削除 (children)
        $CategoryPage->一覧_削除(2);
        $I->acceptPopup();

        // Delete category root
        CategoryManagePage::go($I)->一覧_削除(2);
        $I->acceptPopup();
    }

    public function product_カテゴリ表示順の変更(\AcceptanceTester $I)
    {
        $I->wantTo("EA0309-UC01-T01 カテゴリ表示順の変更");

        $CategoryPage = CategoryManagePage::go($I);
        $I->see('インテリア', $CategoryPage->一覧_名称(2));
        $I->see('キッチンツール', $CategoryPage->一覧_名称(3));
        $I->see('新入荷', $CategoryPage->一覧_名称(4));

        $CategoryPage->一覧_下に(2);
        $I->see('キッチンツール', $CategoryPage->一覧_名称(2));
        $I->see('インテリア', $CategoryPage->一覧_名称(3));
        $I->see('新入荷', $CategoryPage->一覧_名称(4));

        $CategoryPage->一覧_下に(3);
        $I->see('キッチンツール', $CategoryPage->一覧_名称(2));
        $I->see('新入荷', $CategoryPage->一覧_名称(3));
        $I->see('インテリア', $CategoryPage->一覧_名称(4));

        $CategoryPage->一覧_上に(4);
        $I->see('キッチンツール', $CategoryPage->一覧_名称(2));
        $I->see('インテリア', $CategoryPage->一覧_名称(3));
        $I->see('新入荷', $CategoryPage->一覧_名称(4));

        $CategoryPage->一覧_上に(3);
        $I->see('インテリア', $CategoryPage->一覧_名称(2));
        $I->see('キッチンツール', $CategoryPage->一覧_名称(3));
        $I->see('新入荷', $CategoryPage->一覧_名称(4));
    }

    public function product_商品CSV登録(\AcceptanceTester $I)
    {
        $I->wantTo('EA0306-UC01-T01 商品CSV登録');

        ProductManagePage::go($I)->検索('アップロード商品');
        $I->see('検索条件に合致するデータが見つかりませんでした', ProductManagePage::$検索結果_結果なしメッセージ);

        ProductCsvUploadPage::go($I)
            ->入力_CSVファイル('product.csv')
            ->CSVアップロード();
        $I->see('商品登録CSVファイルをアップロードしました', ProductCsvUploadPage::$完了メッセージ);

        ProductManagePage::go($I)->検索('アップロード商品');
        $I->see("検索結果：3件が該当しました", ProductManagePage::$検索結果_メッセージ);
    }

    /**
     * @env firefox
     * @env chrome
     */
    public function product_商品CSV登録雛形ファイルダウンロード(\AcceptanceTester $I)
    {
        $I->wantTo('EA0306-UC01-T02 商品CSV登録雛形ファイルダウンロード');

        ProductCsvUploadPage::go($I)->雛形ダウンロード();
        $ProductTemplateCSV = $I->getLastDownloadFile('/^product\.csv$/');
        $I->assertEquals(1, count(file($ProductTemplateCSV)), 'ヘッダ行だけのファイル');
    }

    public function product_カテゴリCSV登録(\AcceptanceTester $I)
    {
        $I->wantTo('EA0307-UC01-T01(& UC01-T02) カテゴリCSV登録');

        CategoryManagePage::go($I);
        $I->dontSeeElement(['xpath' => '//div[@id="sortable_list_box"]//a[contains(text(), "アップロードカテゴリ")]']);

        CategoryCsvUploadPage::go($I)
            ->入力_CSVファイル('category.csv')
            ->CSVアップロード();

        $I->see('カテゴリ登録CSVファイルをアップロードしました', CategoryCsvUploadPage::$完了メッセージ);

        CategoryManagePage::go($I);

        $I->seeElement(['xpath' => CategoryManagePage::XPathでタグを取得する('アップロードカテゴリ1')]);
        $I->seeElement(['xpath' => CategoryManagePage::XPathでタグを取得する('アップロードカテゴリ2')]);
        $I->seeElement(['xpath' => CategoryManagePage::XPathでタグを取得する('アップロードカテゴリ3')]);
    }

    /**
     * @env firefox
     * @env chrome
     */
    public function product_カテゴリCSV登録雛形ファイルダウンロード(\AcceptanceTester $I)
    {
        $I->wantTo('EA0307-UC01-T02 カテゴリCSV登録雛形ファイルダウンロード');

        // 雛形のダウンロード
        CategoryCsvUploadPage::go($I)->雛形ダウンロード();
        $CategoryTemplateCSV = $I->getLastDownloadFile('/^category\.csv$/');
        $I->assertEquals(1, count(file($CategoryTemplateCSV)), 'ヘッダ行だけのファイル');
    }

    public function product_一覧からの商品確認(\AcceptanceTester $I)
    {
        $I->wantTo('EA0310-UC05-T01 一覧からの商品確認');

        ProductManagePage::go($I)
            ->検索('パーコレーター')
            ->検索結果_確認(1);

        $I->switchToNewWindow();
        $I->seeInCurrentUrl('/products/detail/');
    }
}