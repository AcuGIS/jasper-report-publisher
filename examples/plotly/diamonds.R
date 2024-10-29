library(plotly)
library(ggplot2)
library(htmlwidgets)

set.seed(100)
d <- diamonds[sample(nrow(diamonds), 1000), ]
p <- plot_ly(d, x=~carat, y=~price, text=~paste("Clarity: ", clarity), mode="markers", color=~carat, size=~carat)
htmlwidgets::saveWidget(as_widget(p), file="diamonds.html")