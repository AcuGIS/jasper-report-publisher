library(plotly)
library(ggplot2)
library(RPostgreSQL)
library(htmlwidgets)

conn <- RPostgreSQL::dbConnect("PostgreSQL", host = "localhost", dbname = "$DB_NAME", user = "$DB_USER", password = "$DB_PASS")

query_res <- dbGetQuery(conn, 'select area_id,bee_species,sum(average_harvest) from public.apiary GROUP BY (area_id,bee_species) ORDER BY(area_id)');
area_harvest <- as.data.frame(query_res);

p <- plot_ly(area_harvest, x=~area_id, y=~sum, type="bar",
 		text = ~bee_species, textposition = 'auto') %>%
	layout(title = "Accumulated Average Harvest per Area for Apis Mellifera Carnica",
         xaxis = list(title = "Area ID"), yaxis = list(title = "Average Harvest"))

	
htmlwidgets::saveWidget(as_widget(p), file="index.html")
