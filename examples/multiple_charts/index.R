library(plotly)
library(htmlwidgets)

fig1 <- plot_ly(x = c(1,2,3), y = c(4,5,6), type = 'scatter', mode = 'lines+markers',
                marker = list(line = list(width = 3)))


fig2 <- plot_ly(x = c(20,30,40), y = c(50,60,70), type = 'scatter', mode = 'lines+markers',
                marker = list(line = list(width = 3)))


fig3 <- plot_ly(x = c(300,400,500), y = c(600,700,800), type = 'scatter', mode = 'lines+markers',
                marker = list(line = list(width = 3)))


fig4 <- plot_ly(x = c(4000,5000,6000), y = c(7000,8000,9000), type = 'scatter', mode = 'lines+markers',
                marker = list(line = list(width = 3)))

fig <- subplot(fig1, fig2, fig3, fig4, nrows = 2) %>%
  layout(plot_bgcolor='#e5ecf6',
         xaxis = list(
           zerolinecolor = '#ffff',
           zerolinewidth = 2,
           gridcolor = 'ffff'),
         yaxis = list(
           zerolinecolor = '#ffff',
           zerolinewidth = 2,
           gridcolor = 'ffff'))

htmlwidgets::saveWidget(fig, file = "index.html")
