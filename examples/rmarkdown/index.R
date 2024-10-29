# https://pkgs.rstudio.com/rmarkdown/reference/render.html

rmarkdown::render(input="diamond.Rmd", output_file="diamond.html", output_format="html_document")
rmarkdown::render(input="diamond.Rmd", output_file="diamond.pdf", output_format="pdf_document")
rmarkdown::render(input="diamond.Rmd", output_file="diamond.doc", output_format="word_document")

rmarkdown::render(input="examples.Rmd", output_file="examples.html", output_format="html_document")
rmarkdown::render(input="examples.Rmd", output_file="examples.pdf", output_format="pdf_document")
#rmarkdown::render(input="examples.Rmd", output_file="examples.doc", output_format="word_document")


rmarkdown::render(input="skimr.Rmd", output_file="skimr.html", output_format="html_document")
rmarkdown::render(input="skimr.Rmd", output_file="skimr.pdf", output_format="pdf_document", latex_engine="xelatex")
rmarkdown::render(input="skimr.Rmd", output_file="skimr.doc", output_format="word_document")