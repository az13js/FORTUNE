# FORTUNE

**FORTUNE**是一个基于`Laravel`框架开发的中新网金融频道新闻爬虫，它可以24小时不间断地从中新网金融频道获取最新的新闻信息。

**FORTUNE**分成两个部分。

1. 命令部分。命令部分在后台运行，不断地从中新网爬取新闻列表并和数据库中的内容进行比较。当新闻ID在数据库中没有收录的时候，获取新闻内容并将标题和内容插入数据库。
2. WEB页面部分。默认首页是新闻列表页面，展示新闻标题，和发布日期。点击列表内容后跳转到新闻内容页面。

## FORTUNE的数据库表结构

### `news`表

<table>
<tr><th>字段</th><th>类型</th><th>含义</th></tr>
<tr><td>id</td><td>UNSIGNED BIGINT</td><td>自增主键</td></tr>
<tr><td>version</td><td>UNSIGNED INT</td><td>备用的乐观锁</td></tr>
<tr><td>news_key</td><td>VARCHAR(20)</td><td>新闻key</td></tr>
<tr><td>title</td><td>VARCHAR(50)</td><td>标题</td></tr>
<tr><td>url</td><td>VARCHAR(200)</td><td>链接</td></tr>
<tr><td>public</td><td>DATETIME</td><td>发布时间</td></tr>
<tr><td>created_at</td><td>TIMESTAMP</td><td>创建时间</td></tr>
<tr><td>updated_at</td><td>TIMESTAMP</td><td>更新时间</td></tr>
</table>

### `context`表

<table>
<tr><th>字段</th><th>类型</th><th>含义</th></tr>
<tr><td>id</td><td>UNSIGNED BIGINT</td><td>自增主键</td></tr>
<tr><td>version</td><td>UNSIGNED INT</td><td>备用的乐观锁</td></tr>
<tr><td>news_id</td><td>UNSIGNED INT</td><td>关联的news表id</td></tr>
<tr><td>context</td><td>VARCHAR(200)</td><td>保存新闻的文件名</td></tr>
<tr><td>created_at</td><td>TIMESTAMP</td><td>创建时间</td></tr>
<tr><td>updated_at</td><td>TIMESTAMP</td><td>更新时间</td></tr>
</table>