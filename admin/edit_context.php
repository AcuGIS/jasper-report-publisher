<?php
	session_start(['read_and_close' => true]);
	require('incl/const.php');
	require('class/database.php');
	require('class/report.php');
	require('class/context.php');

	if(!isset($_SESSION[SESS_USR_KEY]) || !in_array($_SESSION[SESS_USR_KEY]->accesslevel, ADMINISTRATION_ACCESS) ){
			header('Location: ../login.php');
			exit;
	}
	
	$database = new Database(DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_SCMA);

	$inp = ['id' => 0, 'name' => '', 'input' => '' , 'report_id' => 0];
	
	if(!empty($_GET['id'])){
		$inp_obj = new context_Class($database->getConn());
		$result = $inp_obj->getById($_GET['id']);
		$inp = pg_fetch_assoc($result);
		pg_free_result($result);
	}

	$rep_obj = new Report_Class($database->getConn());
	$reportRows = $rep_obj->getRows();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<?php include("incl/meta.php"); ?>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.css" />
	<script type="importmap">
	{
	     "imports": {
	         "ckeditor5": "https://cdn.ckeditor.com/ckeditor5/42.0.0/ckeditor5.js",
	         "ckeditor5/": "https://cdn.ckeditor.com/ckeditor5/42.0.0/"
	     }
	}
	</script>
<script>
	$(document).ready(function() {
		
		$('#context_form').submit(false);
		
		$(document).on("click", "#submit", function() {
				var obj = $(this);

				var input = $('#context_form').find('input[type="text"], select, textarea');
				var empty = false;
				input.each(function() {
					if (!$(this).prop('disabled') && $(this).prop('required') && !$(this).val()) {
						$(this).addClass("error");
						empty = true;
					} else {
						$(this).removeClass("error");
					}
				});

				if(empty){
					$('#context_form').find(".error").first().focus();
				}else{
					let form_data = new FormData($('#context_form')[0]);
					form_data.delete('input');	// remove old input value
					form_data.append('input', inputEditor.getData());
					
					$.ajax({
						type: "POST",
						url: 'action/context.php',
						processData: false,
						contentType: false,
						data: form_data,
						dataType:"json",
						success: function(response){
							alert(response.message);
							 if(response.success) {
								 window.location.href = 'contexts.php';
							 }
						 }
					});
				}
		});
	});
</script>
</head>
<body>

    <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
        data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">

        <?php const MENU_SEL = 'edit_context.php';
					include("incl/topbar.php");
					include("incl/sidebar.php");
				?>

        <div class="page-wrapper">
            <div class="page-breadcrumb" style="padding-left:30px; padding-right: 30px; padding-top:0px; padding-bottom: 0px">
                <div class="row align-items-center">
                    <div class="col-6">
                        <nav aria-label="breadcrumb">

                          </nav>
                        <h1 class="mb-0 fw-bold">Context Panel</h1>
                    </div>



                </div>
            </div>
            <div class="container-fluid">
							<form method="post" id="context_form" action="action/context.php" style="max-width: 750px;">

								<input type="hidden" name="save" id="save" value="1"/>
								<?php if(!empty($_GET['id'])){ ?>
								<input type="hidden" name="id" id="id" value="<?=$inp['id']?>"/>
								<?php } ?>

							<div class="form-group">
								<label for="name" style="color: #777e89; font-weight: 500;">Context Name </label>
								<input type="text" name="name" id="name" value="<?=$inp['name']?>" style="border: 1px solid #ccc; border-radius: 4px;"/>
							 </div>
							<div class="form-group">
								<label for="report_id" style="color: #777e89; font-weight: 500;">Select Report:  </label>
								<select name="report_id" id="report_id" style="border: 1px solid #ccc; border-radius: 4px;">
								    <?php while($row = pg_fetch_object($reportRows)) { ?>
								    <option <?php if($inp['report_id'] == $row->id) {?> selected <?php } ?> value="<?=$row->id?>"><?=$row->name?></option>
								    <?php } ?>
								</select>
 							</div>
        			<div class="form-group">
								<textarea name="input" id="input" rows="10" cols="80"><?=$inp['input']?></textarea>
							</div>
							
							<?php if(!empty($_GET['id'])){ ?>
								<input type="submit" id="submit" class="btn btn-primary" value="Update">
							<?php } else { ?>
								<input type="submit" id="submit" class="btn btn-primary" value="Create">
							<?php } ?>
							
							
							</form>

<script>
	var inputEditor;
	$(window).scroll(function() {
		if ( $(document).scrollTop() > $(".hdr_top").outerHeight() ) {
			$('.header_outer').addClass('shrink');
		} else {
			$('.header_outer').removeClass('shrink');
		}
	});
	</script>
	
	<script type="module">
	import {
		ClassicEditor,
	AccessibilityHelp,
	Autoformat,
	AutoLink,
	Autosave,
	BalloonToolbar,
	BlockQuote,
	Bold,
	Code,
	CodeBlock,
	Essentials,
	FindAndReplace,
	Heading,
	Highlight,
	HorizontalLine,
	HtmlEmbed,
	Indent,
	IndentBlock,
	Italic,
	Link,
	Paragraph,
	SelectAll,
	SpecialCharacters,
	SpecialCharactersArrows,
	SpecialCharactersCurrency,
	SpecialCharactersEssentials,
	SpecialCharactersLatin,
	SpecialCharactersMathematical,
	SpecialCharactersText,
	Strikethrough,
	Table,
	TableCellProperties,
	TableProperties,
	TableToolbar,
	TextTransformation,
	Underline,
	Undo
	} from 'ckeditor5';

	ClassicEditor
			.create( document.querySelector( '#input' ), {
					plugins: [ 
						AccessibilityHelp,
						Autoformat,
						AutoLink,
						Autosave,
						BalloonToolbar,
						BlockQuote,
						Bold,
						Code,
						CodeBlock,
						Essentials,
						FindAndReplace,
						Heading,
						Highlight,
						HorizontalLine,
						HtmlEmbed,
						Indent,
						IndentBlock,
						Italic,
						Link,
						Paragraph,
						SelectAll,
						SpecialCharacters,
						SpecialCharactersArrows,
						SpecialCharactersCurrency,
						SpecialCharactersEssentials,
						SpecialCharactersLatin,
						SpecialCharactersMathematical,
						SpecialCharactersText,
						Strikethrough,
						Table,
						TableCellProperties,
						TableProperties,
						TableToolbar,
						TextTransformation,
						Underline,
						Undo
					],
					toolbar: {
							items: [
								'undo',
								'redo',
								'|',
								'heading',
								'|',
								'bold',
								'italic',
								'underline',
								'|',
								'link',
								'insertTable',
								'highlight',
								'blockQuote',
								'codeBlock',
								'|',
								'indent',
								'outdent'
	],
	shouldNotGroupWhenFull: true
					}
			} )
			.then( newEditor => {
        inputEditor = newEditor;
    } )
			.catch( /* ... */ );
</script>

</body>
</html>
