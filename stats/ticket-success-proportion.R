t <- read.table('ticket-statuses.tsv', sep = '\t', header = TRUE,
                row.names = 1)
success <- c(t[1,])
unsuccess <- c(t[2,])
xmax = 29
pdf('clarind-helpdesk-success-proportions.pdf')
plot(cumsum(success) / ((cumsum(unsuccess) + cumsum(success))),
     main="CLARIN+D Helpdesk", ylab="Proportion of succesfully closed tickets",
     xlab = "Weeks",
     type='l', pch = '-', col = 'green',
     xlim = c(1, xmax), ylim = c(0, 1))

summary(t(t))
summary(cumsum(success) / ((cumsum(unsuccess) + cumsum(success))))
