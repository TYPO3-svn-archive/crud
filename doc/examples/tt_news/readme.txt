CRUD example - including browse, show, search, filter and sorting action for tt_news records. Additionally we added a browse as an RSS-feed.

--------------------------------
Make CRUD working - step by step
--------------------------------

1) Install crud 
2) Under 'Info/ Modify' add two static templates to your main template
3) Create two new pages in your page tree. The one is for the list-view and the other one for the RSS-feed.
4) Setup the constants in your TS-template for tx_crud_example

---TypoScript---
plugins.ttnews.browsePid = pid //storage pid for the news
plugins.ttnews.singlePid = pid //single page pid  - could be the same as browsePid
plugins.ttnews.nodes = pid //storage folder for the news
---TypoScript---

5) Create a new content element in the main content area of the type HTML on the site where the list-view should be. Type in the following lines:

---CODE---
<div id="news" class="listnews">
{{{browse~plugin.tt_news}}}
</div>
---CODE---

6) Create another content element in the sidebar of the same page of the type HTML:

---CODE---
<div id="related" class="listnews">
{{{browse~plugin.tt_news_related}}}
</div>
---CODE---

7) Now we want to get the RSS feed working. Switch to the site you have created for. Create a new content element in the main content area of the type HTML and type in:

---CODE---
<div id="rss" class="listnews">
{{{browse~plugin.tt_news_rss}}}
</div>
---CODE---

8) Yahooo! Now the example should work! So have a look at the page within your favourite browser.


---DEVELOPER SECTION--- 
If want to see how CRUD is working please take a look at the templates of the example as well as the models and views of CRUD. There you find the PHP-DOC.
When you look at the TS there you will see that you can browse any TCA based content. Simply change the storage.nameSpace and storage.fields and modify the templates.

An complete documentation will comming soon with more examples. So stay tuned! 
If you have problems/questions feel free to contact me under: f.thelemann@yellowmed.com
