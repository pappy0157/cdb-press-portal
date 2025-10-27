=== CDB Press Portal ===
Contributors: cdb-team
Tags: press, news, portal, ranking, taxonomy, ajax, shortcode, schema, company
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPLv2 or later

プレスリリース特設ページ（/pressportal）を自動生成。ピックアップ・新着・ランキング・SNSで話題・会社連携・タグクラウドAJAXフィルタ・構造化データを提供。

== Description ==
* 有効化時に /pressportal ページを自動作成し、[cdb_press_portal] を挿入
* ショートコード:
  * [cdb_press_portal]
  * [cdb_press_pickup limit="6"]
  * [cdb_press_new limit="12"]
  * [cdb_press_today_rank limit="10"]
  * [cdb_press_month_rank limit="10"]
  * [cdb_press_taxonomy_list taxonomy="category" limit="24"]
  * [cdb_press_social_hot limit="10"]
  * [cdb_press_filter]
  * [cdb_press_companies limit="8"]
* REST: 
  * /wp-json/cdb/v1/new?page=2&per_page=12（新着の無限読み込み）
  * /wp-json/cdb/v1/social-hit（シェアクリック時のスコア加算）
  * /wp-json/cdb/v1/filter?category=it&tag=ai&s=LLM&page=1（AJAXフィルタ）
* 設定画面: 「設定 → Press Portal 設定」で件数・スラッグ・SNS/会社連携を調整
* ランキングは `_today_views` / `_monthly_views` メタを参照。
* 構造化データ: NewsArticle（カードごと）、CollectionPage（ポータル）

== Installation ==
1. Zip化してアップロード or `wp-content/plugins/cdb-press-portal` に配置
2. 有効化すると /pressportal ページが作成されます。
3. 「設定 → Press Portal 設定」で件数・スラッグなどを調整。スラッグ変更後はパーマリンクを更新。

== Notes ==
* `SNSで話題` はデフォルトで「サイト内のシェアボタンクリック」をスコア化します（`_social_score`）。外部カウント連携はフィルタ/独自実装で拡張してください。
* 会社連携は「記事 post_meta `_cdb_company_id` → company post」の紐づけを前提（設定でキー/ポストタイプ変更可）。

== Changelog ==
= 1.1.0 =
- SNSスコア計測、AJAXフィルタ、会社連携、構造化データを実装
- 初期ポータル各セクションのテンプレ安定化

= 1.0.0 =
- 初期公開
