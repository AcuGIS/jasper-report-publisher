library(plotly)
library(htmlwidgets)
library(rjson)

url <- 'https://raw.githubusercontent.com/plotly/datasets/master/geojson-counties-fips.json'
counties <- rjson::fromJSON(file=url)

url2 <- "https://raw.githubusercontent.com/plotly/datasets/master/fips-unemp-16.csv"
df <- read.csv(url2, colClasses=c(fips="character"))

g <- list(
  scope = 'usa',
  projection = list(type = 'albers usa'),
  showlakes = TRUE,
  lakecolor = toRGB('white')
)

p <- plot_ly() %>%
	add_trace(
    type="choropleth",
    geojson=counties,
    locations=df$fips,
    z=df$unemp,
    colorscale="Viridis",
    zmin=0,
    zmax=12,
    marker=list(line=list(
      width=0)
    )
  ) %>%
	colorbar(title = "Unemployment Rate (%)") %>%
	layout(title = "2016 US Unemployment by County") %>%
	layout(geo = g)

htmlwidgets::saveWidget(as_widget(p), file="usa_unemployment_2016.html")