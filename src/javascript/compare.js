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

class compare {

	constructor(obj, config) {
		this.config = Object.assign({
			addcls: "minify__",
			threads: 4,
			dataattr: "compare",
			inputfields: ["input", "inputgzip"],
			outputfields: ["time", "output", "diff", "ratio", "outputgzip", "diffgzip", "ratiogzip"],
			comparefields: ["ratio", "ratiogzip", "time"]
		}, config || {});
		this.config.cls = "."+this.config.addcls;
	}

	run(rows) {
		this.compare = rows;
		const len = this.compare.length,
			promises = [];
		this.resolvers = [];
		this.i = 0;
		this.data = [];

		// create promises for each loop
		for (let p = 0; p < len; p++) {
			promises.push(new Promise(resolve => {
				this.resolvers.push(resolve);
			}));
		}

		// when all promises are resolved
		Promise.allSettled(promises).then(() => {
			this.totals(this.data);
		});


		// set each thread going
		for (let n = 0; n < Math.min(this.config.threads, this.compare.length); n++) {
			this.next();
		}
	}

	format(key, val) {
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

	bestAndWorst(values, flip) {
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

	next() {
		const i = this.i++,
			$this = this.compare.eq(i),
			url = $this.data(this.config.dataattr),
			rows = $this.add($this.next()),
			callback = response => {
				this.resolvers[i](); // resolve this promise
				if (response.ok) {
					response.json().then(json => {
						const metrics = ["best", "worst"];

						// add input sizes
						this.config.inputfields.forEach(key => {
							$(this.config.cls + key, rows).text(this.format(key, json[key]));
						});

						// add output data
						this.config.outputfields.forEach(key => {
							const cells = $(this.config.cls + key, rows),
								minifiers = [],
								results = {},
								values = [];
							let n = 0;
							$.each(json.minifiers, (min, data) => {
								minifiers.push(min);

								// store best and worst
								if (this.config.comparefields.indexOf(key) > -1 && !data.irregular) {
									values.push(data[key]);
								}

								// render the value
								cells.eq(n++).text(this.format(key, data[key]));
							});

							// write best and worst
							if (values.length) {
								$.each(this.bestAndWorst(values, key === "time"), (type, index) => {
									cells.eq(index).addClass(this.config.addcls + "table--" + type);
								});
							}
						});

						// save data to calculate totals/averages
						this.data.push(json);
					});
				}

				// make the next call
				if (this.i < this.compare.length) {
					this.next();
				}
			};
		fetch(location.href + "?index=" + url).then(callback);
	}

	totals(data) {
		let total = $(this.config.cls + "total"),
			avg = $(this.config.cls + "averages"),
			input = {};

		total = total.add(total.next());
		avg = avg.add(avg.next());

		// input fields
		this.config.inputfields.forEach(key => {
			input[key] = 0;

			// tot up the input
			data.forEach(item => {
				input[key] += item[key];
			});

			// render
			$(this.config.cls + key, total).text(this.format(key, input[key]));
			$(this.config.cls + key, avg).text(this.format(key, input[key] / data.length));
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
				this.config.outputfields.forEach(key => {
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
		this.config.outputfields.forEach(key => {
			const totalcells = $(this.config.cls + key, total);
			const avgcells = $(this.config.cls + key, avg),
				totals = [];

			// render values
			values.forEach((item, i) => {
				const suffix = key === "ratiogzip" ? "gzip" : "",
					avg = ["ratio", "ratiogzip"].indexOf(key) > -1,
					val = avg ? (100 / input["input" + suffix]) * item["diff" + suffix] * -1 : item[key];
				totalcells.eq(i).text(this.format(key, val));
				avgcells.eq(i).text(this.format(key, avg ? val : val / len));
				totals.push(val);
			});

			// best and worst
			if (this.config.comparefields.indexOf(key) > -1) {
				$.each(this.bestAndWorst(totals, key === "time"), (type, index) => {
					totalcells.eq(index).add(avgcells.eq(index)).addClass(this.config.addcls + "table--" + type);
				});
			}
		});
	}
}

$(() => {
	$(".minify__start").on("click", () => {
		const obj = new compare();
		obj.run($("[data-compare]"));
	});
});
