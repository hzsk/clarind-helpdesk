#!/bin/bash
bash otrs-csv2tsv.bash 0 < stats/CLARIN+_deliverable_latest.csv \
    > ticket-accumulations.tsv
bash otrs-csv2tsv.bash 0 \
    < stats/CLARIN+_deliverable_successful__close_count_latest.csv \
    > ticket-statuses.tsv
bash otrs-csv2tsv.bash NA < stats/CLARIN+_deliverable_times_latest.csv \
    > ticket-times.tsv

awk -F '\t' '{print NF;}' < ticket-accumulations.tsv
echo "Those are some good values +1 for xmax in ticket-accumulations.R"
select a in yes ; do
    break
done
awk -F '\t' '{print NF;}' < ticket-statuses.tsv
echo "Those are some good values for xmax in ticket-success-proportions.R"
select a in yes ; do
    break
done

R --no-save < ticket-accumulations.R
R --no-save < ticket-success-proportion.R
R --no-save < ticket-times.R
evince clarind-helpdesk.pdf
evince clarind-helpdesk-success-proportions.pdf
evince clarind-helpdesk-times.pdf
