t <- read.table('clarind-helpdesk.tsv', sep = '\t', header = TRUE,
                row.names = 1)
closedsucc <- c(t[1,])
closedunsucc <- c(t[8,])
merged <- c(t[16,])
newnew <- t[24,]
open <- t[32,]
ymax = 200
# rest is empty 2017 but may change
pdf('clarind-helpdesk.pdf')
plot(cumsum(closedsucc), main="CLARIN+D Helpdesk", ylab="Tickets",
     xlab = "Months",
     type = "l", pch="-", col = "green", ylim = c(1, ymax))
points(cumsum(closedunsucc), type = "l", pch = "-", col = "red")
points(cumsum(merged), type = "l", pch = "-", col = "light green")
points(newnew, type="b", col = "blue", pch="o")
points(open, type="b", col = "purple", pch="x")
legend(1, ymax, c("ΣSuccessful", "ΣUnsuccessful", "Merged", "New", "Open"),
                 col = c("green", "red", "light green", "blue", "purple"),
                 pch = c("-", "-", "-", "o", "x"))

