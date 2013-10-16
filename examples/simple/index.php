<?PHP

include "../index.php";

// ========================================================================== //
// SCRIPT
// ========================================================================== //

ob_start();
?>
$(function(){
  
  // Handle form submit.
  $('#params').submit(function(){
    var proxy = '../../ba-simple-proxy.php',
      url = proxy + '?' + $('#params').serialize();
    
    // Update some stuff.
    $('#request').html( $('<a/>').attr( 'href', url ).text( url ) );
    $('#response').html( 'Loading...' );
    
    // Test to see if HTML mode.
    if ( /mode=native/.test( url ) ) {
      
      // Make GET request.
		$.ajax({
	            type:"GET",
	            beforeSend: function (request)
	            {
	                request.setRequestHeader("Authentication-Token", $('#token').val() );
	            },
	            url: url,
	            success: function(data, textStatus, request) {
					if(null != request.getResponseHeader('Authentication-Token') ){
		                $('#token').val(request.getResponseHeader('Authentication-Token'));	
					}

					if(request.getResponseHeader('Content-Type').indexOf("application/json") == 0){
						$('#response')
				          .html( '<pre class="brush:js"/>' )
				          .find( 'pre' )
				            .text( JSON.stringify( data, null, 2 ) );
			        	 SyntaxHighlighter.highlight();				
					}else if(request.getResponseHeader('Content-Type').indexOf("text/html") == 0){
						$('#response')
				          .html( '<pre class="brush:xml"/>' )
				          .find( 'pre' )
				            .html( data );						
					}else{
						$('#response')
				          .html( '<pre class="brush:xml"/>' )
				          .find( 'pre' )
				            .text( data );
				        SyntaxHighlighter.highlight();				
					}
	            }
	    });

    } else {
      
      // Make JSON request.
      $.getJSON( url, function(data){
        
        $('#response')
          .html( '<pre class="brush:js"/>' )
          .find( 'pre' )
            .text( JSON.stringify( data, null, 2 ) );
        
        SyntaxHighlighter.highlight();
      });
    }
    
    // Prevent default form submit action.
    return false;
  });
  
  // Submit the form on page load if ?url= is passed into the example page.
  if ( $('#url').val() !== '' ) {
    $('#params').submit();
  }
  
  // Disable AJAX caching.
  $.ajaxSetup({ cache: false });
  
  // Disable dependent checkboxes as necessary.
  $('input:radio').click(function(){
    var that = $(this),
      c1 = 'dependent-' + that.attr('name'),
      c2 = c1 + '-' + that.val();
    
    that.closest('form')
      .find( '.' + c1 + ' input' )
        .attr( 'disabled', 'disabled' )
        .end()
      .find( '.' + c2 + ' input' )
        .removeAttr( 'disabled' );
  });
  
  // Clicking sample remote urls should populate the "Remote URL" box.
  $('.sample a').click(function(){
    $('#url').val( $(this).attr( 'href' ) );
    return false;
  });
});
<?
$shell['script'] = ob_get_contents();
ob_end_clean();

// ========================================================================== //
// HTML HEAD ADDITIONAL
// ========================================================================== //

ob_start();
?>
<script type="text/javascript" language="javascript">

// I want to use json2.js because it allows me to format stringified JSON with
// pretty indents, so let's nuke any existing browser-specific JSON parser.
window.JSON = null;

</script>
<script type="text/javascript" src="../../shared/json2.js"></script>
<script type="text/javascript" language="javascript">

<?= $shell['script']; ?>

$(function(){
  
  // Syntax highlighter.
  SyntaxHighlighter.defaults['auto-links'] = false;
  SyntaxHighlighter.highlight();
  
});

</script>
<style type="text/css" title="text/css">

/*
bg: #FDEBDC
bg1: #FFD6AF
bg2: #FFAB59
orange: #FF7F00
brown: #913D00
lt. brown: #C4884F
*/

#page {
  width: 700px;
}

#params input.text {
  display: block;
  border: 1px solid #000;
  width: 540px;
  padding: 2px;
  margin-bottom: 0.2em;
}

#params input.submit {
  display: block;
  margin-top: 0.6em;
}

.indent {
  margin-left: 1em;
}

#sample {
  font-size: 90%;
}

</style>
<?
$shell['html_head'] = ob_get_contents();
ob_end_clean();

// ========================================================================== //
// HTML BODY
// ========================================================================== //

ob_start();
?>
<p>
  Note that while jQuery is used here, you can use any library you'd like.. or just code your
  XMLHttpRequest objects by hand, it doesn't matter. This proxy just acts a bridge between the client
  and server to facilitate cross-domain communication, so the client-side JavaScript is entirely left
  up to you (but I recommend jQuery's <a href="http://docs.jquery.com/Ajax/jQuery.getJSON">getJSON</a>
  method because of its simplicity).
</p>
<p>
  Please see the <a href="https://github.com/ets/php-simple-proxy">project page</a> information.
</p>

<h2>Ad-Hoc Testing</h2>

<form id="params" method="get" action="">
  <div>
    <label>
      <b>Remote URL</b>
      <input id="url" class="text" type="text" name="url" value="<?= $_GET['url'] ?>">
    </label>
  </div>
  <p>
    ..try these API requestes in order:
<ol>
	<li class="sample"><a href="https://secure.foldergrid.com/demo">FolderGrid API DEMO Login</a></li>
	<li class="sample"><a href="https://secure.foldergrid.com/folder/*">FolderGrid API Folder Listing</a></li>
    <li class="sample"><a href="https://secure.foldergrid.com/file/610686">Generate a Download URL</a></li>
</ol>
  </p>  
<input type="hidden" name="mode" value="native"/>
<input type="hidden" name="DEVELOPMENT_MODE" value="true"/>
  <input class="submit" type="submit" name="submit" value="Submit">
</form>

<h3>Request URL</h3>
<p id="request">N/A, click Submit!</p>
<h3>Simple PHP Proxy response</h3>
<div id="response">N/A, click Submit!</div>
<h3>Current FolderGrid Authentication Token</h3>
<input id="token" class="text" style="width:600px" type="text" name="token" value="">

<h3>The code</h3>

<pre class="brush:js">
<?= htmlspecialchars( $shell['script'] ); ?>
</pre>

<?
$shell['html_body'] = ob_get_contents();
ob_end_clean();

// ========================================================================== //
// DRAW SHELL
// ========================================================================== //

draw_shell();

?>
