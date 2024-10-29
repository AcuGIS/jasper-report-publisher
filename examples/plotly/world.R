library(plotly)
library(rnaturalearth)
library(htmlwidgets)

world <- ne_countries(returnclass = "sf")

p <- plot_ly(world, color = I("gray90"), stroke = I("black"), span = I(1))
htmlwidgets::saveWidget(as_widget(p), file="world.html")
