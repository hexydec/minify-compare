import $ from "../../node_modules/dabbyjs/src/core/dabby/dabby.js";
import "../../node_modules/dabbyjs/src/core/each/each.js";
import "../../node_modules/dabbyjs/src/utils/each/each.js";
import "../../node_modules/dabbyjs/src/traversal/eq/eq.js";
import "../../node_modules/dabbyjs/src/attributes/data/data.js";
import "../../node_modules/dabbyjs/src/manipulation/text/text.js";
import "../../node_modules/dabbyjs/src/traversal/add/add.js";
import "../../node_modules/dabbyjs/src/traversal/next-prev/next-prev.js";
import "../../node_modules/dabbyjs/src/attributes/class/class.js";
import "../../node_modules/dabbyjs/src/events/on/on.js";

function format(key, val) {
	const format = {
		ratio: val => parseFloat(val).toFixed(2) + "%",
		ratiogzip: val => parseFloat(val).toFixed(2) + "%",
		time: val => parseFloat(val).toFixed(8)
	};
	if (format[key]) {
		return format[key](val);

	// format the other values
	} else {
		return new Intl.NumberFormat("en-GB").format(Math.round(val));
	}
}

function bestAndWorst(values, flip) {
	const metrics = {
		best: null,
		worst: null
	};
	$.each(metrics, m => {
		values.forEach((item, i) => {
			const gt = flip ? m !== "best" : m === "best";
			if (metrics[m] === null || (gt ? values[metrics[m]] < item : values[metrics[m]] > item)) {
				metrics[m] = i;
			}
		});
	});
	return metrics;
}

$(() => {
	$(".minify__start").on("click", () => {
		const compare = $("[data-compare]"),
			threads = 4,
			addcls = "minify__",
			cls = "." + addcls,
			data = [],
			inputfields = ["input", "inputgzip"],
			outputfields = ["time", "output", "diff", "ratio", "outputgzip", "diffgzip", "ratiogzip"],
			comparefields = ["ratio", "ratiogzip", "time"],
			finish = data => {
				let total = $(cls + "total"),
					avg = $(cls + "averages"),
					input = {};

				total = total.add(total.next());
				avg = avg.add(avg.next());

				// input fields
				inputfields.forEach(key => {
					input[key] = 0;

					// tot up the input
					data.forEach(item => {
						input[key] += item[key];
					});

					// render
					$(cls + key, total).text(format(key, input[key]));
					$(cls + key, avg).text(format(key, input[key] / data.length));
				});

				// collate output fields
				let values = [];
				data.forEach((item, n) => {

					// tot up the output
					let i = 0;
					$.each(item.minifiers, (min, row) => {
						if (!values[i]) {
							values[i] = {};
						}
						outputfields.forEach(key => {
							if (!values[i][key]) {
								values[i][key] = 0;
							}
							values[i][key] += row[key];
						});
						i++;
					});
				});

				// render output fields
				const len = data.length;
				outputfields.forEach(key => {
					const totalcells = $(cls + key, total);
					const avgcells = $(cls + key, avg),
						totals = [];

					// render values
					values.forEach((item, i) => {
						const suffix = key === "ratiogzip" ? "gzip" : "",
							avg = ["ratio", "ratiogzip"].indexOf(key) > -1,
							val = avg ? (100 / input["input" + suffix]) * item["diff" + suffix] * -1 : item[key];
						totalcells.eq(i).text(format(key, val));
						avgcells.eq(i).text(format(key, avg ? val : val / len));
						totals.push(val);
					});

					// best and worst
					if (comparefields.indexOf(key) > -1) {
						$.each(bestAndWorst(totals, key === "time"), (type, index) => {
							totalcells.eq(index).add(avgcells.eq(index)).addClass(addcls + "table--" + type);
						});
					}
				});
			};
		let i = 0,
			len = compare.length,
			loop,
			promises = [],
			resolvers = [];

		// create promises for each loop
		for (let p = 0; p < len; p++) {
			promises.push(new Promise((resolve, reject) => {
				resolvers.push([resolve, reject]);
			}));
		}

		loop = () => {
			const curri = i++,
				$this = compare.eq(curri),
				url = $this.data("compare"),
				rows = $this.add($this.next()),
				callback = response => {
					resolvers[curri][0](); // resolve this promise
					if (response.ok) {
						response.json().then(json => {
							const metrics = ["best", "worst"];

							// add input sizes
							inputfields.forEach(key => {
								$(cls + key, rows).text(format(key, json[key]));
							});

							// add output data
							outputfields.forEach(key => {
								const cells = $(cls + key, rows),
									minifiers = [],
									results = {},
									values = [];
								let n = 0;
								$.each(json.minifiers, (min, data) => {
									minifiers.push(min);

									// store best and worst
									if (comparefields.indexOf(key) > -1 && !data.irregular) {
										values.push(data[key]);
									}

									// render the value
									cells.eq(n++).text(format(key, data[key]));
								});

								// write best and worst
								if (values.length) {
									$.each(bestAndWorst(values, key === "time"), (type, index) => {
										cells.eq(index).addClass(addcls + "table--" + type);
									});
								}
							});

							// save data to calculate totals/averages
							data.push(json);
						});
					}

					// make the next call
					if (i < len) {
						loop();
					}
				};
			fetch(location.href + "?index=" + url).then(callback);
		};

		// when all promises are resolved
		Promise.allSettled(promises).then(() => {
			finish(data);
		});

		// set each thread going
		for (n = 0; n < Math.min(threads, compare.length); n++) {
			loop();
		}
	});
});
