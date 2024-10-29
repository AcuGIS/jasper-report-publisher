function tableToCSV(tbl_id, csv_name) {

		// Variable to store the final csv data
		var csv_data = [];
		
		//gets table
		var tbl = document.getElementById(tbl_id);
		
		//gets rows of table
		const rows_len = tbl.rows.length;

		// Get each row data
		for (var i = 0; i < rows_len; i++) {

			//gets cells of current row  
			var cols = tbl.rows.item(i).cells;

			//gets amount of cells of current row
			var cols_len = cols.length;

			//loops through each cell in current row
			var csvrow = [];
			for(var j = 0; j < cols_len; j++){
				// Get the text data of each cell
				// of a row and push it to csvrow
				csvrow.push(cols[j].innerHTML);
			}

			// Combine each column value with comma
			csv_data.push(csvrow.join(","));
		}

		// Combine each row data with new line character
		csv_data = csv_data.join('\n');

		// Call this function to download csv file 
		downloadCSVFile(csv_name, csv_data);

}

function dataToCSV(csv_name, project_data){
	var csv_hdr  = JSON.stringify(Object.keys(project_data.features[0].properties)).replace(/(^\[)|(\]$)/mg, '');
	var csv_data = project_data.features.map(function (feat){
		return JSON.stringify(Object.values(feat.properties));
	}).join('\n') 
	.replace(/(^\[)|(\]$)/mg, '');
	
	// Call this function to download csv file 
	downloadCSVFile(csv_name, csv_hdr + '\n' + csv_data);
}

function downloadCSVFile(csv_name, csv_data) {

		// Create CSV file object and feed
		// our csv_data into it
		CSVFile = new Blob([csv_data], {
				type: "text/csv"
		});

		// Create to temporary link to initiate
		// download process
		var temp_link = document.createElement('a');

		// Download csv file
		
		temp_link.download = csv_name + '.csv';
		var url = window.URL.createObjectURL(CSVFile);
		temp_link.href = url;

		// This link should not be displayed
		temp_link.style.display = "none";
		document.body.appendChild(temp_link);

		// Automatically click the link to
		// trigger download
		temp_link.click();
		document.body.removeChild(temp_link);
}