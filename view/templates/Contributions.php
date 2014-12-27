<div class="page-wrap" id="contributions-wrap">
	<div class="widget widget-summary wleft">
		<div class="widget-header">
			<strong>Contributions</strong>
			<button style="float:right;" class="btn btn-xs btn-default" id="btn-dl-contribCsv">Download CSV</button>
		</div>
		<div class="widget-body">
			<table class="table hide" style="width:100%;text-align:center;">
				<tr id="contribTab-tr-th">
					<th><input type="checkbox" class="contrib-select-all" /> Team Member</th>
					<th>Contribution</th>
					<th>Project</th>
					<th>Value</th>
					<th>Date</th>
					<th>Description</th> <!-- append :~project~ -->
					<th>Slices</th>
				</tr>
				<tr id="contribTab-tr-model" class="table-record">
					<td class="contribTab-grunt grunt-name">
						<input type="checkbox" class="cbox-contribs" />
						<span class="contribTab-grunt-name"></span>
					</td>
					<td class="contribTab-contri">0</td>
					<td class="contribTab-proj">0</td>
					<td class="contribTab-value">0</td>
					<td class="contribTab-date">0</td>
					<td class="contribTab-desc">0</td>
					<td class="contribTab-points">0</td>
				</tr>
			</table>
			<select id="contribSort-selector">
				<option value="-1">Sort By:</option>
				<option value="0">Contribution</option>
				<option value="1">Slices</option>
				<option value="2">Team Member</option>
				<option value="3">Project</option>
			</select>
			<div>
				<span style="color:#696969;margin:auto;float:left"><span class="glyphicon glyphicon-info-sign" style="margin:8px;"></span>Right-click on the contribution to edit or delete.</span>
            	<div class="btn-group" data-toggle="buttons" style="float:right;">
				  <label class="btn btn-xs btn-default" style="margin-top:10px;">
				    <input id="cb-sdc" type="checkbox"> <span id="btn-sdc">Show Deleted Contributions</span>
				  </label>
				</div>
            </div>
            <div style="width:100%; height:10px; clear:both;"></div>
			<table class="table">
				<tbody id="contrib-list-table">
				</tbody>
			</table>
		</div>
	</div>

</div>
<script type="text/javascript">
	$("body").on("change", ".cbox-contribs", function() {
		if($(this).is(":checked")) {
			$(this).parent().parent().css("background", "#FFFFCC");
			if($(".cbox-contribs:not(:checked)").length == 0)
				$(".contrib-select-all").prop('checked', true);
		}
		else {
			$(this).parent().parent().css("background", "");
			$(".contrib-select-all").prop('checked', false);
		}

	});
	$("body").on("click", ".contrib-select-all", function() {
		if($(this).is(":checked"))
			$(".cbox-contribs").prop('checked', true);
		else
			$(".cbox-contribs").prop('checked', false);
		$(".cbox-contribs").change();
	});
	$("#cb-sdc").change(function() {
		if($(this).is(":checked")) {
			$("#btn-sdc").html("Hide Deleted Contributions");
		} else {
			$("#btn-sdc").html("Show Deleted Contributions");
		}
			$(".deleted").fadeToggle(300);
	});
</script>