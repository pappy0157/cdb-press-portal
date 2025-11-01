=== CDB Press Portal ===
Contributors: cdb-team
Tags: press, news, portal, ranking, taxonomy, ajax, shortcode, schema, company, slider, thumbnails
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.3.0
License: GPLv2 or later

プレスリリース特設ページ（/pressportal）を自動生成。ヒーローをスライダーに、直下へタグクラウドAJAXフィルタを配置。管理画面で投稿と会社（post_type=company）をメタボックスから紐づけ可能。

== Changelog ==
= 1.3.0 =
- ヒーロー直下に「タグクラウドAJAXフィルタ」を配置（`[cdb_press_filter]`）
- 投稿編集画面に「会社を選択」メタボックスを追加（会社検索・選択→保存で `_cdb_company_id` に保存）
- フィルタは REST `/wp-json/cdb/v1/filter` を利用、結果はカードUIで非同期描画
