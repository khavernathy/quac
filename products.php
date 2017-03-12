<?php 
/*
	products.php
	Made by Douglas Franz, freelance PHP/MySQL/HTML/CSS/JS/jQuery-ist.
*/

session_start();
date_default_timezone_set('America/New_York');
ob_implicit_flush(true);
include('models/auth.php');
include('includes/calendar.php');
include('includes/database.php');
include('includes/datetime_functions.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Quac Admin Area</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/__jqft.js"></script>
	<script type="text/javascript">
	
		function deleteConfirm()
		{
			if (typeof prevProdHTMLid === 'undefined' || prevProdHTMLid == '')
			{ alert('No product is selected!'); return false; }
			else 
			{
				if ($("#newProd").length == 0)
				{
					var delProdName = $("#"+prevProdHTMLid+" td:nth-child(4) input").val();
					if (confirm('Are you sure you want to delete '+delProdName+'? This action cannot be undone.'))
					{
						// delete the product
						$.ajax(
						{
							url: "ajax/echoDeleteProduct.php?selected_id=" + prevProdHTMLid,
							type: 'GET',
							dataType: 'json',
							success: function(data)
							{
								if (data == "Deleted the product successfully")
								{ window.location = "products.php?cpd";	}
								else {
									$("#statusBox").html("ERROR: Failed to delete the product.");
									$("#statusBox").fadeIn();
									setTimeout(function(){
										$("#statusBox").fadeOut();
									}, 7000);
								}
							}
						});
					}
				}
				else
				{
					alert('Cannot delete a new and unsaved product.');
				}
			}
		}
		
		function checkForm()
		{
			var num_regex = /^[0-9]+([\,\.][0-9]+)?$/;
			if ($("#Name").val() == '' || $("#Retail").val() == '')
			{ alert('You must at least include a product name and retail value.'); return false; }
			else if (!(num_regex.test($("#Retail").val())) || ($("#Wholesale").val() != '' && !(num_regex.test($("#Wholesale").val()))))
			{ alert('Retail and Wholesale fields must contain numerical values (decimals allowed)'); return false;}
			
			else {return true;}
		}
		
		function openEdits(prodHTMLid)
		{
			if (window.prodHTMLid == prodHTMLid) {return false;}
			else 
			{
				if (window.prevProdHTMLid) {
					if (window.prevProdHTMLid == "newProd") {
						// delete the new product addition row
						$("#"+window.prevProdHTMLid).remove();
						document.productForm.action = "products.php?save";
					} else {
						// replace form inputs with ORIGINAL text on previous row
						$("#"+prevProdHTMLid+" td:nth-child(2)").html(prevProdMakerID);
						$("#"+prevProdHTMLid+" td:nth-child(3)").html(prevProdMaker);
						$("#"+prevProdHTMLid+" td:nth-child(4)").html(prevProdName);
						$("#"+prevProdHTMLid+" td:nth-child(5)").html(prevProdSize);
						$("#"+prevProdHTMLid+" td:nth-child(6)").html(prevProdRetail);
						$("#"+prevProdHTMLid+" td:nth-child(7)").html(prevProdWholesale);
						$("#"+prevProdHTMLid+" td:nth-child(8)").html(prevProdCategory);
						$("#"+prevProdHTMLid+" td:nth-child(9)").html(prevProdVendor);
						$("#"+prevProdHTMLid+" td:nth-child(10)").html(prevProdStockCount);
						$("#"+prevProdHTMLid+" td:nth-child(11)").html(prevProdTaxable);
						$("#"+prevProdHTMLid+" td:nth-child(12)").html(prevProdInactive);
						$("#"+prevProdHTMLid+" td:nth-child(13)").html("");
					}
				}
				// set new "previous id" and corresponding values (so that the user doesn't assume a previous row was saved)
				window.prevProdHTMLid = prodHTMLid;
				window.prevProdMakerID = $("#"+prodHTMLid+" td:nth-child(2)").html();
				window.prevProdMaker = $("#"+prodHTMLid+" td:nth-child(3)").html();
				window.prevProdName = $("#"+prodHTMLid+" td:nth-child(4)").html();
				window.prevProdSize = $("#"+prodHTMLid+" td:nth-child(5)").html();
				window.prevProdRetail = $("#"+prodHTMLid+" td:nth-child(6)").html();
				window.prevProdWholesale = $("#"+prodHTMLid+" td:nth-child(7)").html();
				window.prevProdCategory = $("#"+prodHTMLid+" td:nth-child(8)").html();
				window.prevProdVendor = $("#"+prodHTMLid+" td:nth-child(9)").html();
				window.prevProdStockCount = $("#"+prodHTMLid+" td:nth-child(10)").html();
				window.prevProdTaxable = $("#"+prodHTMLid+" td:nth-child(11)").html();
				window.prevProdInactive = $("#"+prodHTMLid+" td:nth-child(12)").html();
				
				// set current id
				window.prodHTMLid = prodHTMLid;
				var sid = prodHTMLid.replace("productSel","");
				
				
				var midText = $("#"+prodHTMLid+" td:nth-child(2)").html();
				$("#"+prodHTMLid+" td:nth-child(2)").html("<input type='hidden' name='selectedID' id='selectedID' value='"+sid+"' /><input type='text' size='8' name='MakerID' id='MakerID' value='" + midText + "' />");
				
				var makText = $("#"+prodHTMLid+" td:nth-child(3)").html();
				$("#"+prodHTMLid+" td:nth-child(3)").html("<input type='text' name='Maker' id='Maker' value='" + makText + "' />");
				
				var nameText = $("#"+prodHTMLid+" td:nth-child(4)").html();
				$("#"+prodHTMLid+" td:nth-child(4)").html("<input type='text' size='38' name='Name' id='Name' value='" + nameText + "' />");
				
				var sizeText = $("#"+prodHTMLid+" td:nth-child(5)").html();
				$("#"+prodHTMLid+" td:nth-child(5)").html("<input type='text' size='8' name='Size' id='Size' value='" + sizeText + "' />");
				
				var retailText = parseFloat($("#"+prodHTMLid+" td:nth-child(6)").html()).toFixed(2);
				$("#"+prodHTMLid+" td:nth-child(6)").html("<input type='text' size='5' name='Retail' id='Retail' value='" + retailText + "' />");
				
				var wholesaleText = parseFloat($("#"+prodHTMLid+" td:nth-child(7)").html()).toFixed(2);
				$("#"+prodHTMLid+" td:nth-child(7)").html("<input type='text' size='5' name='Wholesale' id='Wholesale' value='" + wholesaleText + "' />");
				
				var catText = $("#"+prodHTMLid+" td:nth-child(8)").html();
				$("#"+prodHTMLid+" td:nth-child(8)").html("<input type='text' name='Category' id='Category' value='" + catText + "' />");
				
				var vendorText = $("#"+prodHTMLid+" td:nth-child(9)").html();
				$("#"+prodHTMLid+" td:nth-child(9)").html("<input type='text' name='Vendor' id='Vendor' value='" + vendorText + "' />");
				
				var scText = $("#"+prodHTMLid+" td:nth-child(10)").html();
				$("#"+prodHTMLid+" td:nth-child(10)").html("<input type='text' size='3' name='StockCount' id='StockCount' value='" + scText + "' />");
				
				var taxText = $("#"+prodHTMLid+" td:nth-child(11)").html();
				if (taxText == "yes") {
					$("#"+prodHTMLid+" td:nth-child(11)").html("<input type='checkbox' name='Tax' id='Tax' checked />");
				} else {
					$("#"+prodHTMLid+" td:nth-child(11)").html("<input type='checkbox' name='Tax' id='Tax' />");
				}
				
				var InactiveText = $("#"+prodHTMLid+" td:nth-child(12)").html();
				if (InactiveText == "yes") {
					$("#"+prodHTMLid+" td:nth-child(12)").html("<input type='checkbox' name='Inactive' id='Inactive' checked />");
				} else {
					$("#"+prodHTMLid+" td:nth-child(12)").html("<input type='checkbox' name='Inactive' id='Inactive' />");
				}
				
				$("#"+prodHTMLid+" td:nth-child(13)").html('<input type="submit" value="save" name="productFormSubmit" id="productFormSubmit" />');
			}
			
		}
		
		function hideSearch()
		{
			$("#statusBox").fadeOut();
			document.getElementById('productSearchName').value = '';
		}
		
		function scrollToAndSelect(HTMLid)
		{
			HTMLid = HTMLid.replace("csr","productSel");
			var row = document.getElementById(HTMLid);
			var c = row.getAttribute("class");
			c = parseInt(c.replace("r",""));
			$('#product-list-box').scrollTop((c*18)-(18*3));
			
			openEdits(HTMLid);
		}
		
		// function to handle live search
		function runLiveSearch() {
		
			var search_string = $(this).val();

			if ($('#productSearchName').val() == '') {
				$("#statusBox").fadeOut();
			}else{
				search_string = $('#productSearchName').val();
				$.ajax({
					url: "ajax/echoProductSearchResults.php?query="+search_string,
					type: 'GET',
					dataType: 'json',
					success: function(data) {
						var filler = '';
						if (data && data.length > 0) {
							if (data.length < 30) {
							filler = '<span class="searchInfoBar">Found '+data.length+' results for "'+search_string+'" [<a href="#" onclick="hideSearch();">close search</a>]</span> <br />'; }
							else { filler = '<span class="searchInfoBar">Showing first 30 results for "'+search_string+'" [<a href="#" onclick="hideSearch();">close search</a>]</span> <br />'; }
							filler = filler + '<table id="search-results" cellpadding="2" border="1" cellspacing="1"><tr><td>ID</td><td>Maker ID</td><td>Maker</td><td>Name</td><td>Size</td><td>Retail</td><td>Wholesale</td><td>Stock Count</td></tr>';
							for (x=0;x <= (data.length - 1);x++)
							{
								filler = filler +'<tr class="searchLink" id="csr'+data[x][0]+'" onclick="scrollToAndSelect(this.id);"><td>'+data[x][0]+'</td><td>' + data[x][1] + '</td><td>' + data[x][2] + '</td><td>'+data[x][3]+'</td><td>'+data[x][4]+'</td><td>'+data[x][5]+'</td><td>'+data[x][6]+'</td><td>'+data[x][7]+'</td></tr>';
							}
							filler = filler + '</table>';
						} else { filler = '<span class="searchInfoBar">No search results found for "'+search_string+'". [<a href="#" onclick="hideSearch();">close search</a>]</span>'; }
						document.getElementById('statusBox').innerHTML = filler;
					}
				});
				$("#statusBox").fadeIn();
			}		
		}
		
		function transferDataToTicket(rowID)
		{
			if (rowID == '' || (typeof rowID === 'undefined')) {
				alert('No product was selected!');
			} else {
				var tdrID = <?php if (isset($_GET['tdrID'])) { echo json_encode($_GET['tdrID']); } else { echo '""'; } ?>;
				var hrID = <?php if (isset($_GET['hrID'])) { echo json_encode($_GET['hrID']); } else { echo '""'; } ?>;
				//window.opener.$("body").html('This is the rowID of the product: ' + rowID + '<br />This is the ticket detail ID: ' + tdrID + '<br />This is the HTML row ID on the ticket page: ' + hrID + '');
				
				if (hrID != 'none') {
					window.opener.$("#" + hrID + " td:nth-child(3)").html("0"); // no duration for product
					window.opener.$("#" + hrID + " td:nth-child(4)").html("P"); // p for product
					window.opener.$("#" + hrID + " td:nth-child(5)").html(rowID.replace("productSel",""));
					window.opener.$("#" + hrID + " td:nth-child(6)").html($("#" + rowID + " td:nth-child(4) input").val()); // description/name
					window.opener.$("#" + hrID + " td:nth-child(7)").html($("#" + rowID + " td:nth-child(6) input").val()); // price
					if ($("#" + rowID + " td:nth-child(11) input").is(':checked')) // tax box
					{
						window.opener.$("#" + hrID + " td:nth-child(8) input").prop('checked', 'true');
					}
					window.opener.$("#" + hrID + " td:nth-child(9)").html("1"); // qty
					window.opener.$("#" + hrID + " td:nth-child(10)").html($("#" + rowID + " td:nth-child(6) input").val()); // total (same as price)
					
					window.opener.saveRowValues();
				
				} else if (hrID == 'none') {
					alert('hrID is none');
				}
				window.close();
			}
		}
		
		$(document).ready(function() {
			// live search event handlers
			$('#productSearchName').keyup(runLiveSearch);
			
			//float table headers
			var $table = $('table.products');
			$table.floatThead({
				useAbsolutePositioning: false,
				scrollContainer: function($table) {
					return $table.closest('#product-list-box');
				}
			});
		});

	</script>
	<link rel="stylesheet" type="text/css" href="css/admin.css" />
	</head>
<body class="products">
	<div id="admin_header">	
		<?php include('includes/menu.php'); ?>
	</div>	
	<div id="admin_content">
		<h1>Products &raquo;
		<a class="buttonLink" href="products.php?new">
			<img class="button" src="images/addProduct.png" alt="create new product" title="create new product" height="32px" width="32px" /></a>
			
		<a class="buttonLink" href="#" onclick="deleteConfirm();">
		<img class="button" src="images/deleteProduct.png" alt="deleted selected product" title="deleted selected product" height="32px" width="32px" /></a>
		
		<!--
		<a class="buttonLink" href="#">
		<img class="button" src="images/recieveProduct.jpg" alt="receive products" title="receive products" height="32px" width="32px" /></a>
		-->
		
		<?php 
		if (isset($_GET['popupSearch']))
		{
			echo '<a class="buttonLink" id="transferButton" onclick="transferDataToTicket(window.prevProdHTMLid);">
		<img class="button" src="images/transfer.png" alt="move selected product to current ticket" title="move selected product to current ticket" height="32px" width="32px" /></a>';
		}
		?>
	
		&raquo;
		
		<img class="button" src="images/magnifyingGlass.png" height="32px" width="32px" />
		
		<span><form id="productSearchForm" name="productSearchForm" action="" onsubmit="return false;">
		Name: <input type="text" name="productSearchName" id="productSearchName" />
		</form></span>
		</h1>
		
			<hr />
			<div id="statusBox"></div>
			<?php
			echo '<div id="product-list-box">';
		
			if (!isset($_GET['sort'])) {
				$getproducts = "SELECT * FROM `products`";
			} else {
				$getproducts = "SELECT * FROM `products` ORDER BY ".$_GET['sort']." ".$_GET['order'];
			}
			$stmt = $Database->prepare($getproducts);
			if ($stmt->execute())
			{
				$result = $stmt->get_result();
				
				echo "<form name='productForm' id='productForm' method='POST' action='products.php?save".(isset($_GET['new'])?'&new':'')."' onsubmit='return checkForm();'>";
				echo '<table class="products" cellpadding="0" border="0" cellspacing="0"><thead>';
				echo '<tr>
					<th><a href="products.php?sort=ProductID&order='.((isset($_GET['sort']) && $_GET['sort'] == "ProductID" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">ID</a></th>
					<th><a href="products.php?sort=ManfID&order='.((isset($_GET['sort']) && $_GET['sort'] == "ManfID" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Maker ID</a></th>
					<th><a href="products.php?sort=Maker&order='.((isset($_GET['sort']) && $_GET['sort'] == "Maker" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Maker</a></th>
					<th><a href="products.php?sort=Name&order='.((isset($_GET['sort']) && $_GET['sort'] == "Name" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Name</a></th>
					<th><a href="products.php?sort=Size&order='.((isset($_GET['sort']) && $_GET['sort'] == "Size" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Size</a></th>
					<th><a href="products.php?sort=Retail&order='.((isset($_GET['sort']) && $_GET['sort'] == "Retail" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Retail</a></th>
					<th><a href="products.php?sort=Wholesale&order='.((isset($_GET['sort']) && $_GET['sort'] == "Wholesale" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Wholesale</a></th>
					<th><a href="products.php?sort=Category&order='.((isset($_GET['sort']) && $_GET['sort'] == "Category" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Category</a></th>
					<th><a href="products.php?sort=Vendor&order='.((isset($_GET['sort']) && $_GET['sort'] == "Vendor" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Vendor</a></th>
					<th><a href="products.php?sort=StockCount&order='.((isset($_GET['sort']) && $_GET['sort'] == "StockCount" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Stock Count</a></th>
					<th><a href="products.php?sort=Taxable&order='.((isset($_GET['sort']) && $_GET['sort'] == "Taxable" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Taxable</a></th>
					<th><a href="products.php?sort=Inactive&order='.((isset($_GET['sort']) && $_GET['sort'] == "Inactive" && $_GET['order'] == "ASC") ? 'DESC':'ASC').'">Inactive</a></th>
					<th>[save]</th>
					</tr></thead><tbody>';

				if (isset($_GET['new'])) {
					echo "<tr id='newProd'><td></td>
					<td><input type='text' size='8' name='MakerID' id='MakerID' value='' /></td>
					<td><input type='text' name='Maker' id='Maker' value='' /></td>
					<td><input type='text' size='38' name='Name' id='Name' value='' /></td>
					<td><input type='text' size='8' name='Size' id='Size' value='' /></td>
					<td><input type='text' size='5' name='Retail' id='Retail' value='' /></td>
					<td><input type='text' size='5' name='Wholesale' id='Wholesale' value='' /></td>
					<td><input type='text' name='Category' id='Category' value='' /></td>
					<td><input type='text' name='Vendor' id='Vendor' value='' /></td>
					<td><input type='text' size='3' name='StockCount' id='StockCount' value='' /></td>
					<td><input type='checkbox' name='Tax' id='Tax' /></td>
					<td><input type='checkbox' name='Inactive' id='Inactive' /></td>
					<td><input type='submit' value='save' name='productFormSubmit' id='productFormSubmit' /></td></tr>";
					?>
						<script type="text/javascript">
							window.prevProdHTMLid = "newProd";
						</script>
					<?php
				}
				for ($n = 0; $n <= ($result->num_rows - 1); $n++) 
				{
					$result->data_seek($n);
					$row = $result->fetch_array();
					
					$htmlID = 'productSel'.$row[0];
					echo '<tr id="'.$htmlID.'" class="r'.($n+1).(($row[12] == "1")?" grayout":"").'" onclick="openEdits(this.id);">
					<td>'.$row[0].'</td><td>'.$row[1].'</td><td>'.$row[2].'</td><td>'.$row[3].'</td><td>'.$row[4].'</td><td>'.number_format((float)$row[5], 2, '.', '').'</td><td>'.number_format((float)$row[6], 2, '.', '').'</td><td>'.$row[7].'</td><td>'.$row[8].'</td><td>'.$row[9].'</td><td>'.(($row[10] == "1")?"yes":"no").'</td><td>'.(($row[12] == "1")?"yes":"no").'</td><td></td></tr>'; //}
				}
				$result->close();
				echo '</tbody></table>';
				echo "</form>";
			}
			else
			{
				echo "Query to retrieve all products' info failed";
			}
			echo '</div>';
			
			if (isset($_GET['save']))
			{
				if (!isset($_GET['new'])) {$sid = $_POST['selectedID'];}
				else { $sid = ''; }
				$MakerID = $_POST['MakerID'];
				$Maker = $_POST['Maker'];
				$Name = $_POST['Name'];
				$Size = $_POST['Size'];
				$Retail = $_POST['Retail'];
				$Wholesale = $_POST['Wholesale'];
				$Category = $_POST['Category'];
				$Vendor = $_POST['Vendor'];
				$StockCount = $_POST['StockCount'];
				if (isset($_POST['Tax']) && $_POST['Tax'] == "on") { $Tax = "1"; } else { $Tax = "0";}
				if (isset($_POST['Inactive']) && $_POST['Inactive'] == "on") { $Inactive = "1"; } else { $Inactive = "0";}
				$BackBar = "0";
				$qbid = null;
				
				if (!isset($_GET['new'])) {
					$saveService = 'REPLACE INTO products 
					SET `ProductID` = ?, `ManfID` = ?, `Maker` = ?, `Name` = ?, `Size` = ?, `Retail` = ?, `Wholesale` = ?, `Category` = ?, `Vendor` = ?, `StockCount` = ?, `Taxable` = ?, `BackBar` = ?, `Inactive` = ?, `QuickbooksID` = ?';
				} else {
					$saveService = 'INSERT INTO products VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)';
				}
				
				$stmt = $Database->prepare($saveService);
				$stmt->bind_param('ssssssssssssss',$sid,$MakerID,$Maker,$Name,$Size,$Retail,$Wholesale,$Category,$Vendor,$StockCount,$Tax,$BackBar,$Inactive,$qbid);
				if ($stmt->execute())
				{
					$stmt->close();
					?>
					<script type="text/javascript">
						window.location = "products.php?cpa";
					</script>
					<?php
				}
				else
				{
					?>
					<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "ERROR: Could not execute MySQL statement. Product info was not successfully saved.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 7000);
					</script>
					<?php
				}
			}
			elseif (isset($_GET['cpa']))
			{
				unset($_GET['cpa']);
				?>
				<script type="text/javascript">
					document.getElementById('statusBox').innerHTML = "Saved product info successfully.";
					$("#statusBox").fadeIn();
					setTimeout(function(){
						$("#statusBox").fadeOut();
					}, 2500);
				</script>
				<?php
			}
			elseif (isset($_GET['cpd']))
			{
				unset($_GET['cpd']);
				?>
				<script type="text/javascript">
				document.getElementById('statusBox').innerHTML = "Deleted the product successfully.";
				$("#statusBox").fadeIn();
				setTimeout(function(){
					$("#statusBox").fadeOut();
				}, 2500);
				</script>
				<?php
			}
			?>
			
			
	</div>
</body>
</html>