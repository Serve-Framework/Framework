<style>
	/* RESET */
	html{color:#000;background:#FFF}body,div,dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,code,form,fieldset,legend,input,textarea,p,blockquote,th,td{margin:0;padding:0}table{border-collapse:collapse;border-spacing:0}fieldset,img{border:0}address,caption,cite,code,dfn,em,strong,th,var{font-style:normal;font-weight:normal}ol,ul{list-style:none}caption,th{text-align:left}h1,h2,h3,h4,h5,h6{font-size:100%;font-weight:normal}q:before,q:after{content:''}abbr,acronym{border:0;font-variant:normal}sup{vertical-align:text-top}sub{vertical-align:text-bottom}input,textarea,select{font-family:inherit;font-size:inherit;font-weight:inherit;*font-size:100%}legend{color:#000}#yui3-css-stamp.cssreset{display:none}
	
	/* HTML/BODY */
	*, :after, :before
	{
	    -webkit-box-sizing: border-box;
	    box-sizing: border-box;
	}
	html
	{
	   	font-size: 62.5%;
	}
	body
	{
	    font-family: "Helvetica Neue", Helvetica, -apple-system, system-ui, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
		font-size: 1.45rem;
	}
	html,body
	{
		background: #3f3f3f;
	    color: #c8c8c8;
	    -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
	}

	/* LISTS */
	ul
	{
	    display: block;
	    list-style-type: disc;
	   	margin: 0 0;
	   	padding: 0 0 0 40px;
	}
	li
	{
	    display: list-item;
	}
	.dl-horizontal dt
	{
        float: left;
	    text-align: right;
	    overflow: hidden;
	    text-overflow: ellipsis;
	    white-space: nowrap;
	    width: 85px;
	    clear: left;
	    font-weight: 600;
	}
	.dl-horizontal dd
	{
	   	margin-left: 100px;
	}

	/* TYPOGRAPHY */
	p
	{
		margin: 1em 0;
		padding: 0;
	}
	pre, code
	{
        font-family: Menlo,Monaco,Consolas,"Courier New",monospace;
	    line-height: 1.8;
	    font-size: 1.2rem;
	    -webkit-font-smoothing: initial;
	    -moz-osx-font-smoothing: initial;
	}
	code
	{
	    font-size: 1.4rem;
	}
	p code
	{
		padding: .2rem .4rem;
	}
	a
	{
	    color: rgb(17, 85, 204);
	    text-decoration: none;
	}
	strong
	{
		font-weight: 600;
	}
	h1, h2, h3, h4, h5, h6
	{
		margin-bottom: 16px;
		font-weight: normal;
	}
	h1
	{
	    font-size: 3rem;
	}
	h2
	{
	    font-size: 2rem;
	}
	h3
	{
	    font-size: 1.8rem;
	}
	h4
	{
	    font-size: 1.6rem;
	}
	h5
	{
	    font-size: 1.4rem;
	}
	h6
	{
	    font-size: 1.3rem;
	}
	.uppercase
	{
		text-transform: uppercase;
	}

	/* BUTTON */
	button,
	.button
	{
	  background: #c942ff;
	  border: 0;
	  border-radius: 2px;
	  color: #fff;
	  cursor: pointer;
	  font-size: .875em;
	  margin: 0;
	  padding: 10px 24px;
	  transition: box-shadow 200ms cubic-bezier(0.4, 0, 0.2, 1);
	  font-weight: bold;
	  user-select: none;
	}
	button:hover,
	.button:hover
	{
	  box-shadow: 0 1px 3px rgba(0, 0, 0, .50);
	}
	button:active,
	.button:active
	{
	  background: #8a2caf;
	  outline: 0;
	  box-shadow: none;
	}

	/* LAYOUT */
	.row
	{
		width: 100%;
		display: block;
		padding-top: 15px;
		padding-bottom: 15px;
	}
	.row:after
	{
	  content: "";
	  display: table;
	  clear: both;
	}		
	.interstitial-wrapper
	{
		width: 100%;
		max-width: 680px;
	    padding-top: 100px;
	    margin: 0 auto;
	    margin-bottom: 90px;
	    overflow: hidden;
	    padding-left: 10px;
	    padding-right: 10px;
	}

	/* STYLES */
	.icon
	{
		background-repeat: no-repeat;
	    background-size: 100%;
	    height: 72px;
	    margin: 0 0 40px;
	    width: 72px;
	   	user-select: none;
	    display: inline-block;
	    position: relative;
		content: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAJAAAACQBAMAAAAVaP+LAAAAGFBMVEUAAABTU1NNTU1TU1NPT09SUlJSUlJTU1O8B7DEAAAAB3RSTlMAoArVKvVgBuEdKgAAAJ1JREFUeF7t1TEOwyAMQNG0Q6/UE+RMXD9d/tC6womIFSL9P+MnAYOXeTIzMzMzMzMzaz8J9Ri6HoITmuHXhISE8nEh9yxDh55aCEUoTGbbQwjqHwIkRAEiIaG0+0AA9VBMaE89Rogeoww936MQrWdBr4GN/z0IAdQ6nQ/FIpRXDwHcA+JIJcQowQAlFUA0MfQpXLlVQfkzR4igS6ENjknm/wiaGhsAAAAASUVORK5CYII=);
	}
	.error-msg
	{
		font-size: 1.3rem;
		color: #00ffac;
	    background: #27242b;
	    border-radius: 3px;
	    padding: 12px;
	    margin: 10px 0;
    	display: block;
	}
	.error-desc
	{
	    font-size: 1.3rem;
	}
	
	/* CODE BLOCK */
	.code-block pre
	{
		white-space: normal;
		overflow-y: auto;
		border: none;
	    border-radius: 5px;
	    background: #27242b;
	}
	.code-block pre code
	{
		display: block;
		color: #e8e8e8;
		background: #27242b;
	}
	.code-block .line
	{
		position: relative;
		padding-left: 55px;
	}
	.code-block .lineno
	{
		border-right: 1px dotted #696969;
	    position: absolute;
	    left: 0;
	    top: 0;
	    padding: 0 10px 0 10px;
	    color: #696969;
	}
	.code-block .linecode
	{
		white-space: pre;
		padding-right: 20px;
	}
	.line.error
	{
		background-color: #ab3a3a;
		color: #fff;
		display: table;
		width: 100%;
	}
	.line.error .lineno
	{
		color: #fff;
		border-right: 1px dotted #e82222;
	}
	.trace-list
	{
		color: #00ffac;
	    background: #27242b;
		border-radius: 5px;
		white-space: nowrap;
    	overflow-x: auto;
    	padding: 15px 40px;
	}
	.trace-list li
	{
		margin-bottom: 4px;
	}
 	.code-block pre::-webkit-scrollbar,
 	.trace-list::-webkit-scrollbar
    {
        width: 10px;
        height: 10px;
    }
    .code-block pre::-webkit-scrollbar-track,
    .trace-list::-webkit-scrollbar-track
    {
        border-radius: 10px;
        background: #27242b;
        box-shadow: 0 0 1px 1px #111, inset 0 0 4px rgba(0,0,0,0.3);
    }
    .code-block pre::-webkit-scrollbar-thumb,
    .trace-list::-webkit-scrollbar-thumb
    {
        border-radius: 10px;
        background: #3b383e;
        box-shadow: none;
    }

</style>
