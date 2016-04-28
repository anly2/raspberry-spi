
<div class="footer">
	<div class="container text-center">
		<nav class="footer-nav nav">
			<ul class="horizontal">
				<li><a href="?">Home</a></li>
				<li><a href="?logout">Logout</a></li>
			</ul>
		</nav>

		<div class="copyright">
			<ul class="authors">
				<li><a href="#">Anko Anchev</a></li>
				<li><a href="#">Yordan Ganchev</a></li>
			</ul>
			<span class="statement">Copyright © 2016 - 2016</span>
		</div>
	</div>
</div>

<script type="text/javascript">
(function(){
	var footer = document.querySelector(".footer");
	var contentHeight = document.body.clientHeight
	var footerTop = footer.offsetTop;

	if (contentHeight > footerTop)
		footer.style.position = "static";
})();
</script>

</body>
</html>