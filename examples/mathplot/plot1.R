library(R3port)

set.seed(1919)                                 # Create example data
x1 <- rnorm(1000)
y1 <- x1 + rnorm(1000)

group <- rbinom(1000, 1, 0.3) + 1              # Create group variable

pl <- function() {
	plot(x1, y1,                                   # Create plot with groups
     main = "This is my Plot",
     xlab = "X-Values",
     ylab = "Y-Values",
     col = group,
     pch = group)
		 
 legend("topleft",                              # Add legend to plot
      legend = c("Group 1", "Group 2"),
      col = 1:2,
      pch = 1:2)
}

html_plot(pl(),	out="index.html")
