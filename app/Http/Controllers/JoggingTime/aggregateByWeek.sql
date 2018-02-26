SELECT
  strftime('%W', day) WeekNumber,
  max(date(day, 'weekday 0', '-7 day')) first_day,
  max(date(day, 'weekday 0', '-1 day')) last_day,
  count(*) AS GroupedValues,
  SUM(distance_m) AS distance_m,
  SUM(minutes) AS minutes
FROM jogging_times
GROUP BY WeekNumber;
